<?php

namespace Domains\Estabelecimento\Controllers;

use Domains\Estabelecimento\BLL\EstabelecimentoBLL;
use Domains\Estabelecimento\Requests\EstabelecimentoRequest;

use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;

class EstabelecimentoController extends BaseController
{
    public function __construct(private readonly EstabelecimentoBLL $estabelecimentoBLL)
    {
        parent::__construct();
        $this->setBll($this->estabelecimentoBLL);
        $this->setRequest('request', EstabelecimentoRequest::class);
    }
    // 👉 methods
    
}
