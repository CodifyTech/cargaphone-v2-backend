<?php

namespace Domains\Anuncio\BLL;

use Domains\Anuncio\Services\AnuncioService;
use Domains\Shared\BLL\BaseBLL;

class AnuncioBLL extends BaseBLL
{
    public function __construct(private readonly AnuncioService $anuncioService)
    {
        $this->setService($this->anuncioService);
    }
    // 👉 methods
    
}
