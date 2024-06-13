<?php

namespace Domains\Auth\Services;

use Domains\Auth\Models\Role;
use Domains\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Domains\Shared\Utils\IntHelper;
use Domains\Shared\Services\BaseService;

class UserService extends BaseService
{
    public function __construct(private readonly User $user, private readonly Role $role)
    {
        $this->setModel($this->user);
    }

    public function roles($options) {
        $data = $this->role->select(['title', 'name AS value'])->paginate(IntHelper::tryParser($options['per_page'] ?? 15) ?? 15);

        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }

    public function store(array $data)
    {
        try{
            \DB::beginTransaction();
            $usuario = $this->user->create($data);
            $usuario->assignRole($data['role']['value']);
            \DB::commit();

            // Carregar o relacionamento 'roles' para o usuário
            $usuario->load('roles:id,name,title');

            // Obter o nome do primeiro papel e atribuí-lo à propriedade 'perfil'
            $usuario->role = $usuario->roles->pluck('name')->first();

            return $usuario;
        } catch (\Exception $exception){
            \DB::rollBack();
            throw new \Exception('Ocorreu um erro ao cadastrar o usuário');
        }
    }

    public function update(array $data, string $id)
    {
        $user = $this->findById($id);
        if(isset($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

        $roles = isset($data['role']['value']) ? $data['role']['value'] : [];
        $user->syncRoles($roles);

        return $user;
    }
}
