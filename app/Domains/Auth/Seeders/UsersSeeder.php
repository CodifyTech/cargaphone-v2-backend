<?php

namespace Domains\Auth\Seeders;

use Domains\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $users = [
            [
                'data' => [
                    'name' => 'Admin',
                    'email' => 'admin@admin.com',
                    'password' => Hash::make('123456'),
                ],
                'roleName' => 'admin',
            ],
            [
                'data' => [
                    'name' => 'Usuário',
                    'email' => 'user@codifytech.com.br',
                    'password' => Hash::make('123456'),
                ],
                'roleName' => 'user',
            ],
        ];

        foreach ($users as $user) {
            $adminUser = User::create($user['data']);
            $adminUser->assignRole($user['roleName']);
        }
    }
}
