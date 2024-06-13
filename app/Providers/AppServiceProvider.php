<?php

namespace App\Providers;

use Domains\Auth\Models\PersonalAccessToken;
use Domains\Auth\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->environment('local')) {
            URL::forceScheme('https');
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        JsonResource::withoutWrapping();
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return env('FRONT_END_URL')."/auth/redefinir-senha?email={$user->email}&token=$token";
        });
    }
}
