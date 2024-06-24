<?php

namespace Domains\Estabelecimento\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class EstabelecimentoRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            "nome" => "string|max:40|required",
            "razao_social" => "string|max:40",
            "documento_legal" => "string|max:50",
            "cnpj" => "string|max:18",
            "segmentacao" => "sometimes",
            "responsavel" => "string|max:60|required",
            "email_responsavel" => "string|max:35|required",
            "telefone_responsavel" => "string|max:15",
            "cep" => "string|max:10|required",
            "endereco" => "string|max:50|required",
            "numero" => "string",
            "cidade" => "string|max:30|required",
            "complemento" => "string|max:30",
            "estado" => "string|max:2|required",
            "data_ativacao" => "date_format:Y-m-d|required"
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
