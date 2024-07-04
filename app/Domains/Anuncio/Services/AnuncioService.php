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
    public function listarTotems($options)
    {
        $data = \Domains\Totem\Models\Totem::query()->paginate($options['per_page'] ?? 15);
        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }
}
