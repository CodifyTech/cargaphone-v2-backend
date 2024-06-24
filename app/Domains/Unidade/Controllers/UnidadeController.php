<?php

namespace Domains\Unidade\Controllers;

use Domains\Unidade\BLL\UnidadeBLL;
use Domains\Unidade\Requests\UnidadeRequest;

use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;

class UnidadeController extends BaseController
{
    public function __construct(private readonly UnidadeBLL $unidadeBLL)
    {
        parent::__construct();
        $this->setBll($this->unidadeBLL);
        $this->setRequest('request', UnidadeRequest::class);
    }
    // 👉 methods
    
}
