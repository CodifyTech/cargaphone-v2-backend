<?php

namespace Domains\Anuncio\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class AnuncioRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            "nome" => "string|max:60|required",
"arquivo" => "string|max:req"
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
