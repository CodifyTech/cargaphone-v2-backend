<?php

namespace Domains\Estabelecimento\Services;

use Domains\Estabelecimento\Models\Estabelecimento;
use Domains\Shared\Services\BaseService;

class EstabelecimentoService extends BaseService
{
    public function __construct(private readonly Estabelecimento $estabelecimento)
    {
        $this->setModel($this->estabelecimento);
    }
    // 👉 methods
    
}
