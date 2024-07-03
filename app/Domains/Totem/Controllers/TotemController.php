<?php

namespace Domains\Totem\Controllers;

use Domains\Totem\BLL\TotemBLL;
use Domains\Totem\Requests\TotemRequest;

use Domains\Shared\Controller\BaseController;
use Illuminate\Http\Request;

class TotemController extends BaseController
{
    public function __construct(private readonly TotemBLL $totemBLL)
    {
        parent::__construct();
        $this->setBll($this->totemBLL);
        $this->setRequest('request', TotemRequest::class);
    }

    // 👉 methods
    public function listarEstabelecimento(Request $request)
    {
        $options = $request->all();
        return $this->totemBLL->listarEstabelecimento($options);
    }

    public function listarUnidade(Request $request)
    {
        $options = $request->all();
        return $this->totemBLL->listarUnidade($options);
    }

    public function totemsEAnuncios(Request $request)
    {
        return $this->totemBLL->totemsEAnuncios($request->all());
    }
}
