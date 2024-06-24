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
    public function listarEstabelecimento($options) {
		$data = \Domains\Estabelecimento\Models\Estabelecimento::query()->paginate($options['per_page'] ?? 15);
		return [
			'data' => $data->items(),
			'total' => $data->total(),
			'page' => $data->currentPage(),
		];
	}
public function listarUnidade($options) {
		$data = \Domains\Unidade\Models\Unidade::query()->paginate($options['per_page'] ?? 15);
		return [
			'data' => $data->items(),
			'total' => $data->total(),
			'page' => $data->currentPage(),
		];
	}
}
