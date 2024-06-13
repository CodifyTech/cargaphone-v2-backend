<?php

namespace Domains\Auth\Controllers;

use Domains\Auth\BLL\UserBLL;
use Domains\Auth\Requests\UserRequest;

use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(private readonly UserBLL $userBLL)
    {
        parent::__construct();
        $this->setBll($this->userBLL);
        $this->setRequest('request', UserRequest::class);
    }

    public function roles(Request $request) {
        $options = $request->all();

        return $this->userBLL->roles($options);
    }
}
