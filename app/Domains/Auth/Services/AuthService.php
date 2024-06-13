<?php

namespace Domains\Auth\Services;

use App\Domains\Auth\Requests\ForgotPasswordRequest;
use App\Domains\Auth\Requests\ResetPasswordRequest;
use Arr;
use Domains\Auth\Models\User;
use Domains\Auth\Requests\LoginRequest;
use Domains\Auth\Requests\RegisterRequest;
use Domains\Shared\Services\BaseService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;

class AuthService extends BaseService
{
    public function __construct(private readonly User $user)
    {
        $this->setModel($this->user);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $this->user->where('email', $request->email)->first();

        if (!$credentials || !Hash::check($request->password, $credentials->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (config('cdf.delete_previous_access_tokens_on_login', false)) {
            $credentials->tokens()->delete();
        }

       $permissions = [];
       $subjects = [];
       foreach ($credentials->getAllPermissions()->pluck('name') as $permission){
           list($subject, $action) = explode(' ', $permission);
           $permissions[] = [
               'subject' => $subject,
               'action' => $action
           ];
           $subjects[] = $subject;
       }

        $authorization = $this->respondWithToken($credentials, 'cdf-api-token', [
            'role' => $credentials->getRoleNames()[0] ?? [],
            'permissions' => $permissions,
            'subjects' => array_unique($subjects)
        ]);

        return response()->json([
            'user' => Arr::except($credentials->toArray(), ['permissions', 'roles']),
            'authorization' => $authorization
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if(! empty($request->roles)) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'user' => $user
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $message = [];
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status == Password::RESET_THROTTLED){
            $message['message'] = 'Tentativa de reinicialização acelerada.';
            $message['status'] = false;
        }
        if($status == Password::INVALID_USER){
            $message['message'] = 'Usuário não existe';
            $message['status'] = false;
        }

        return $message;
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $message = [];

        Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                $status = event(new PasswordReset($user));

                if($status == "passwords.reset"){
                    $message['message'] = 'Senha alterada com sucesso!';
                    $message['status'] = true;
                } else {
                    $message['message'] = 'Ocorreu um error ao alterar a senha!';
                    $message['status'] = false;
                }
            }
        );

        return $message;
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Desconectado com sucesso.'
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param User $user
     * @param $name
     * @param array $abilities
     * @return array
     */
    protected function respondWithToken(User $user, $name, array $abilities = ['*']): array
    {
        $authorization = $user->createToken($name, $abilities);

        return [
            'token' => $authorization->plainTextToken,
            'abilities' => $authorization->accessToken->abilities
        ];
    }
}
