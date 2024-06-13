<?php

namespace Domains\Auth\Seeders;

use App\Domains\Auth\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Domains\Auth\Models\Role;
use Domains\Auth\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        collect(config('permission_list'))->each(function ($permissionValue, $permissionTitle) {
            foreach ($permissionValue as $value) {
                Permission::create(['title' => $permissionTitle, 'name' => $value]);
            }
        });

        collect(RoleEnum::cases())->each(function ($roleEnum) {
            Role::create(['title' => $roleEnum->getRoleName(), 'name' => $roleEnum->value]);
        });

        Role::all()->map(function ($role) {
            collect(RoleEnum::from($role->name)->getPermissions())
            ->each(function ($p) use (&$role) {
                $permission = Permission::findByName($p);
                $role->givePermissionTo($permission);
                if ($permission->hasRole($role))
                    $permission->assignRole($role);
            });
        });
    }
}
