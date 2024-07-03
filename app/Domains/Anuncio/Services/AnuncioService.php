<?php

namespace Domains\Anuncio\Services;

use Domains\Anuncio\Models\Anuncio;
use Domains\Shared\Services\BaseService;

class AnuncioService extends BaseService
{
    public function __construct(private readonly Anuncio $anuncio)
    {
        $this->setModel($this->anuncio);
    }
    // 👉 methods
    
}
