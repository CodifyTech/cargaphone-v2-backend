<?php

namespace Domains\Totem\Services;

use Domains\Totem\Models\Totem;
use Domains\Shared\Services\BaseService;

class TotemService extends BaseService
{
    public function __construct(private readonly Totem $totem)
    {
        $this->setModel($this->totem);
    }
    // 👉 methods
    public function listarEstabelecimento($options)
    {
        $data = \Domains\Estabelecimento\Models\Estabelecimento::query()->paginate($options['per_page'] ?? 15);
        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }

    public function listarUnidade($options)
    {
        $data = \Domains\Unidade\Models\Unidade::query()->paginate($options['per_page'] ?? 15);
        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }

    public function totemsEAnuncios($options)
    {
        $totem = $this->totem->where('identificador', $options['totem'])->first();
        if (!$totem) {
            return [];
        }
        return $totem->anuncios()
            ->select('arquivo as url', 'ativo as exclude', 'updated_at as dataAlteracao')
            ->where('ativo', '=', 1)
            ->get()
            ->map(function ($anuncio, $index) {
                if ($anuncio->exclude) {
                    $anuncio->exclude = false;
                }
                $anuncio['index'] = $index + 0;
                unset($anuncio->pivot);
                return $anuncio;
            });
    }
}
