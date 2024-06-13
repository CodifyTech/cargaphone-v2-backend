<?php

namespace App\Domains\Auth\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case User = 'user';

    public function getPermissions(): array
    {
        return match ($this) {
            self::Admin => [
                ...config('permission_list.auth'),
                ...config('permission_list.manage'),
            ],
            self::User => [
                ...config('permission_list.auth'),
            ],
        };
    }

    public function getRoleName(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::User => 'Usuário',
        };
    }
}
