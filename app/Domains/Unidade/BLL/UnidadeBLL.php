<?php

namespace Domains\Unidade\BLL;

use Domains\Unidade\Services\UnidadeService;
use Domains\Shared\BLL\BaseBLL;

class UnidadeBLL extends BaseBLL
{
    public function __construct(private readonly UnidadeService $unidadeService)
    {
        $this->setService($this->unidadeService);
    }
    // 👉 methods
    
}
