<?php

namespace Domains\Auth\Controllers;

use Domains\Auth\BLL\PermissionBLL;
use Domains\Auth\Requests\PermissionRequest;
use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;
use Domains\Auth\Models\Permission;
use Domains\Auth\Models\User;

class PermissionController extends BaseController
{
    function __construct(private readonly PermissionBLL $permissionBLL)
    {
        parent::__construct();
        $this->setBll($this->permissionBLL);
        $this->setRequest('request', PermissionRequest::class);
    }

    public function listAll(){
        return $this->permissionBLL->listAll();
    }

    public function listarAcoes(){
        return $this->permissionBLL->listarAcoes();
    }

    public function destroyAll(Request $request)
    {
        return $this->permissionBLL->destroyAll($request);
    }

    public function atribuirUserPermission(User $user, Permission $permission)
    {
        return $this->permissionBLL->atribuirUserPermission($user, $permission);
    }

    public function removerUserPermission(User $user, Permission $permission)
    {
        return $this->permissionBLL->removerUserPermission($user, $permission);
    }
}
