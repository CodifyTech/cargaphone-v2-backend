<?php

namespace Domains\Totem\BLL;

use Domains\Totem\Services\TotemService;
use Domains\Shared\BLL\BaseBLL;

class TotemBLL extends BaseBLL
{
    public function __construct(private readonly TotemService $totemService)
    {
        $this->setService($this->totemService);
    }
    // 👉 methods
    public function listarEstabelecimento($options)
    {
        return $this->totemService->listarEstabelecimento($options);
    }
    public function listarUnidade($options)
    {
        return $this->totemService->listarUnidade($options);
    }

    public function totemsEAnuncios($options)
    {
        return $this->totemService->totemsEAnuncios($options);
    }
}
