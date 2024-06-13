<?php

namespace Domains\Auth\BLL;

use Domains\Auth\Services\RoleService;
use Domains\Shared\BLL\BaseBLL;

class RoleBLL extends BaseBLL
{
    public function __construct(private readonly RoleService $roleService)
    {
        $this->setService($this->roleService);
    }
}
