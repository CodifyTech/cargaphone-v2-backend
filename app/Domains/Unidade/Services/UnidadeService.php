<?php

namespace Domains\Unidade\Services;

use Domains\Unidade\Models\Unidade;
use Domains\Shared\Services\BaseService;

class UnidadeService extends BaseService
{
    public function __construct(private readonly Unidade $unidade)
    {
        $this->setModel($this->unidade);
    }
    // 👉 methods
    
}
