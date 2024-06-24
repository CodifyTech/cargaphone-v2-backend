<?php

namespace Domains\Unidade\Models;

use Illuminate\Database\Eloquent\Model;
use Domains\Shared\Traits\Uuid;


use Domains\Totem\Models\Totem;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    use Uuid;

    protected $fillable = [
        'nome',
		'cnpj_empresa',
		'email',
		'nome_responsavel',
		'vindi_costumer_id',
		'dt_abertura',
		'ativo',
		'nome_rua',
		'numero',
		'cep',
		'cidade',
		'estado',
		'softDeletes',
    ];

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $table = 'unidades';


	public function totens(): HasMany
	{
 		return $this->hasMany(Totem::class);
	}
}