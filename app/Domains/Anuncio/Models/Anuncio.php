<?php

namespace Domains\Anuncio\Models;

use Illuminate\Database\Eloquent\Model;
use Domains\Shared\Traits\Uuid;
use Domains\Totem\Models\Totem;
use Domains\Unidade\Models\Unidade;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Anuncio extends Model
{
    use Uuid;

    protected $fillable = [
        'nome',
        'arquivo',
        'nome_anunciante',
        'valor_anuncio_mensal',
        'data_comeco_campanha',
        'data_fim_campanha',
        'tipo_campanha',
        'ativo',
        'tel_contato_anunciante',
        'email_contato',
        'totem_id',
        'tenant_id'
    ];

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $table = 'anuncios';
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Storage::disk('s3')->url('anuncios/' . $value),
        );
    }

    protected function arquivo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Storage::disk('s3')->url('anuncios/' . $value),
        );
    }

    public function totems(): BelongsToMany
    {
        return $this->belongsToMany(Totem::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->BelongsTo(Unidade::class);
    }
}
