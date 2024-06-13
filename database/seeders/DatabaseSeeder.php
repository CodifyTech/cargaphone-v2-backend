<?php

namespace Database\Seeders;

use Domains\Auth\Seeders\AuthDomainDatabaseSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AuthDomainDatabaseSeeder::class);
    }
}
