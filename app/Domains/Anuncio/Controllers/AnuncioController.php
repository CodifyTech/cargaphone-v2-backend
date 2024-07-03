<?php

namespace Domains\Anuncio\Controllers;

use Domains\Anuncio\BLL\AnuncioBLL;
use Domains\Anuncio\Requests\AnuncioRequest;

use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;

class AnuncioController extends BaseController
{
    public function __construct(private readonly AnuncioBLL $anuncioBLL)
    {
        parent::__construct();
        $this->setBll($this->anuncioBLL);
        $this->setRequest('request', AnuncioRequest::class);
    }
    // 👉 methods
    
}
