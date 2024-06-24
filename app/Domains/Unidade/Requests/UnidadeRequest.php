<?php

namespace Domains\Unidade\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class UnidadeRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            "nome" => "string|max:60|required",
            "cnpj_empresa" => "string|max:20",
            "email" => "string|max:30",
            "nome_responsavel" => "string|max:60|required",
            "vindi_costumer_id" => "string",
            "dt_abertura" => "date",
            "ativo" => "boolean|max:default(1)",
            "nome_rua" => "string|max:50",
            "numero" => "integer",
            "cep" => "string|max:10",
            "cidade" => "string|max:30",
            "estado" => "string|max:2",
            "softDeletes" => "string"
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
