<?php

namespace Domains\Auth\Services;

use Domains\Auth\Models\Role;
use Domains\Auth\Models\User;
use Domains\Auth\Models\Permission;
use App\Domains\Shared\Utils\IntHelper;
use Domains\Shared\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleService extends BaseService
{
    public function __construct(private readonly Role $role, private readonly User $user)
    {
        $this->setModel($role);
    }

    public function index(array $options = []): LengthAwarePaginator|array
    {
        $data = $this->role
            ->select(['id', 'name', 'title', 'created_at'])
            ->whereNot('name', 'Admin')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->whereNot('name', 'admin');
            })
            ->paginate(IntHelper::tryParser($options['per_page'] ?? 15) ?? 15);

        return [
            'data' => $data->items(),
            'total' => $data->total(),
        ];
    }

    public function store(array $data)
    {
        $data['name'] = \Str::slug($data['title']);
        $role = Role::create($data);

        $role->givePermissionTo('Auth read');

        if (!empty($data['permissions'])) {
            foreach ($data['permissions'] as $id) {
                $permission = Permission::find($id);
                $role->givePermissionTo($permission->name);
                $permission->syncRoles($role);
            }
        }

        return $role;
    }

    public function show(string $id)
    {
        $permissions = [];

        $data = $this->role->where('id', $id)
            ->with(['permissions' => function ($query) use (&$permissions) {
                $permissions = Permission::permissionsFormat($query)->toArray();
            }])
            ->first()
            ->toArray();

        $data['permissions'] = $permissions;

        return $data;
    }

    public function update(array $data, string $id)
    {
        $role = $this->findById($id);
        $role->update([
            'title' => $data['title'],
            'name' => \Str::slug($data['title']),
            'guard_name' => 'web'
        ]);

        $permissions = $data['permissions'] ?? [];
        $role->syncPermissions($permissions);

        return $role;
    }

    public function atribuirUserRole($userId, $roleId)
    {
        $user = $this->user->find($userId);
        $role = $this->role->find($roleId);

        $user->assignRole($role->name);

        return JsonResource::make($user);
    }

    public function removerUserRole($userId, $roleId)
    {
        $user = $this->user->find($userId);
        $role = $this->role->find($roleId);

        $user->removeRole($role->name);

        return JsonResource::make($user);
    }

    public function atribuirUserRolePermission($userId, $roleId)
    {
        try {
            \DB::beginTransaction();
            $user = $this->user->find($userId);
            $role = $this->role->find($roleId);

            $user->assignRole($role);
            $user->givePermissionTo(Role::findByName($role->name)->permissions()->pluck('name')->toArray());
            $user->refresh();
            \DB::commit();

            return JsonResource::make($user);
        } catch (\Exception) {
            \DB::rollBack();

            return response()->json(['message' => 'Erro ao atribuir permissão ao usuário'], 500);
        }
    }

    public function removeUserRolePermission($userId, $roleId)
    {
        try {
            $user = $this->user->find($userId);
            $role = $this->role->find($roleId);

            $user->removeRole($role->name);
            $user->revokePermissionTo(Role::findByName($role->name)->permissions()->pluck('name')->toArray());

            return JsonResource::make($user);
        } catch (\Exception) {
            \DB::rollBack();

            return response()->json(['message' => 'Erro ao remoção permissão ao usuário'], 500);
        }
    }
}
