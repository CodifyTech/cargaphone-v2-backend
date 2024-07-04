<?php

namespace Domains\Anuncio\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class AnuncioRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            'nome' => [
                'required',
            ],
            'arquivo' => [
                'required',
            ],
            'nome_anunciante' => [
                'required',
            ],
            'valor_anuncio_mensal' => [
                'required',
            ],
            'data_comeco_campanha' => [
                'required',
            ],
            'data_fim_campanha' => [
                'required',
            ],
            'tipo_campanha' => [
                'required',
            ],
            'tel_contato_anunciante' => [
                'required',
            ],
            'email_contato' => [
                'required',
                'email',
                'max:40',
            ],
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
