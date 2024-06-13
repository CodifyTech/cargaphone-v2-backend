<?php

namespace Domains\Auth\BLL;

use Domains\Auth\Services\PermissionService;
use Domains\Shared\BLL\BaseBLL;

class PermissionBLL extends BaseBLL
{
    public function __construct(private readonly PermissionService $permissionService)
    {
        $this->setService($this->permissionService);
    }

    public function listAll() {
        return $this->permissionService->listAll();
    }

    public function listarAcoes() {
        return $this->permissionService->listarAcoes();
    }

    public function destroyAll($data)
    {
        return $this->permissionService->destroyAll($data);
    }

    public function atribuirUserPermission($user, $permission)
    {
        return $this->permissionService->atribuirUserPermission($user, $permission);
    }

    public function removerUserPermission($user, $permission)
    {
        return $this->permissionService->removerUserPermission($user, $permission);
    }
}
