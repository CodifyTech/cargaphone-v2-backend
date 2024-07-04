<?php

namespace Domains\Totem\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class TotemRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            "nome" => "string|max:40|required",
            "descricao" => "string|max:100",
            "ip" => "string|max:100",
            "latitude" => "string|max:50",
            "longitude" => "string|max:50",
            "ultima_conexao" => "string",
            "conexao_id" => "string|max:100",
            "ativo" => "boolean|max:default(1)",
            "estabelecimento_id" => "string",
            "tenant_id" => "string"
        ];
    }

    public function view(): array
    {
        return [];
    }

    public function store(): array
    {
        return [];
    }

    public function update(): array
    {
        return [];
    }

    public function destroy(): array
    {
        return [];
    }
}
