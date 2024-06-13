<?php

namespace Domains\Auth\BLL;

use App\Domains\Auth\Requests\ForgotPasswordRequest;
use App\Domains\Auth\Requests\ResetPasswordRequest;
use Domains\Auth\Services\AuthService;
use Domains\Auth\Requests\LoginRequest;
use Domains\Auth\Requests\RegisterRequest;
use Domains\Shared\BLL\BaseBLL;

class AuthBLL extends BaseBLL
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(LoginRequest $payload)
    {
        return $this->authService->login($payload);
    }

    public function forgotPassword(ForgotPasswordRequest $payload)
    {
        return $this->authService->forgotPassword($payload);
    }

    public function resetPassword(ResetPasswordRequest $payload)
    {
        return $this->authService->resetPassword($payload);
    }

    public function register(RegisterRequest $payload)
    {
        return $this->authService->register($payload);
    }

    public function profile()
    {
        return $this->authService->profile();
    }

    public function logout()
    {
        return $this->authService->logout();
    }
}
