<?php

namespace Domains\Estabelecimento\Models;

use Illuminate\Database\Eloquent\Model;
use Domains\Shared\Traits\Uuid;






use Domains\Totem\Models\Totem;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estabelecimento extends Model
{
    use Uuid;

    protected $fillable = [
        'nome',
		'razao_social',
		'documento_legal',
		'cnpj',
		'segmentacao',
		'responsavel',
		'email_responsavel',
		'telefone_responsavel',
		'cep',
		'endereco',
		'numero',
		'cidade',
		'complemento',
		'estado',
		'data_ativacao',
    ];

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $table = 'estabelecimentos';
	
	public function totens(): HasMany
	{
 		return $this->hasMany(Totem::class);
	}
}