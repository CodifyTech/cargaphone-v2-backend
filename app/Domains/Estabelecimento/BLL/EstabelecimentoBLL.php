<?php

namespace Domains\Estabelecimento\BLL;

use Domains\Estabelecimento\Services\EstabelecimentoService;
use Domains\Shared\BLL\BaseBLL;

class EstabelecimentoBLL extends BaseBLL
{
    public function __construct(private readonly EstabelecimentoService $estabelecimentoService)
    {
        $this->setService($this->estabelecimentoService);
    }
    // 👉 methods
    
}
