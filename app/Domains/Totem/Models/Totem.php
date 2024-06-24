<?php

namespace Domains\Totem\Models;

use Illuminate\Database\Eloquent\Model;
use Domains\Shared\Traits\Uuid;

use Domains\Estabelecimento\Models\Estabelecimento;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Domains\Unidade\Models\Unidade;
class Totem extends Model
{
    use Uuid;

    protected $fillable = [
        'nome',
		'identificador',
		'descricao',
		'ip',
		'latitude',
		'longitude',
		'ultima_conexao',
		'conexao_id',
		'ativo',
		'estabelecimento_id',
		'tenant_id',
    ];

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $table = 'totens';

	public function estabelecimento(): BelongsTo
	{
 		return $this->belongsTo(Estabelecimento::class);
	}
	public function unidade(): BelongsTo
	{
 		return $this->belongsTo(Unidade::class);
	}
}