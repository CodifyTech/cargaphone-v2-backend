<?php

namespace Domains\Auth\Controllers;

use App\Domains\Auth\Requests\ForgotPasswordRequest;
use App\Domains\Auth\Requests\ResetPasswordRequest;
use Domains\Auth\BLL\AuthBLL;
use Domains\Auth\Requests\LoginRequest;
use Domains\Auth\Requests\RegisterRequest;
use Domains\Shared\Controller\BaseController;

class AuthController extends BaseController
{
    public function __construct(private readonly AuthBLL $authBLL)
    {
        parent::__construct();

        $this->setBll($this->authBLL);
    }

    /**
     * Get the authenticated User.
     *
     * @param LoginRequest $request
     */
    public function login(LoginRequest $request)
    {
        return $this->authBLL->login($request);
    }


    /**
     * Create the authenticated User.
     *
     * @param RegisterRequest $request
     */
    public function register(RegisterRequest $request)
    {
        return $this->authBLL->register($request);
    }

    public function forgotPassword(ForgotPasswordRequest $payload)
    {
        return $this->authBLL->forgotPassword($payload);
    }

    public function resetPassword(ResetPasswordRequest $payload)
    {
        return $this->authBLL->resetPassword($payload);
    }

    /**
     * Get the authenticated User.
     *
     */
    public function profile()
    {
        return $this->authBLL->profile();
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        return $this->authBLL->logout();
    }
}
