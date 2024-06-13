<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function register(){

    }

    public function boot(): void
    {
        $dirName = app_path()."\Domains\*\Migrations\\".config('cdf.api_version')."\\*\*.{php}";
        $files = glob($dirName, GLOB_BRACE);
        foreach($files as $file) {
            $this->loadMigrationsFrom($file);
        }
    }
}
