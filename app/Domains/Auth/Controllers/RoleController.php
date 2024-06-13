<?php

namespace Domains\Auth\Controllers;

use Domains\Auth\BLL\RoleBLL;
use Domains\Auth\Requests\RoleRequest;

use Domains\Shared\Controller\BaseController;

class RoleController extends BaseController
{
    public function __construct(private readonly RoleBLL $roleBLL)
    {
        parent::__construct();
        $this->setBll($this->roleBLL);
        $this->setRequest('request', RoleRequest::class);
    }
}
