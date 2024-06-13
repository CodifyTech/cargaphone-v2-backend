<?php

namespace Domains\Auth\Requests;

use Domains\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [
            'foto' => ['nullable'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function view(): array
    {
        return [];
    }

    public function store(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role.value' => ['required', 'exists:\Domains\Auth\Models\Role,name'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function update(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->request->get('id')],
            'role.value' => ['required', 'exists:\Domains\Auth\Models\Role,name'],
            'password' => ['nullable','confirmed', Password::defaults()],
        ];
    }

    public function destroy(): array
    {
        return [];
    }

    public function attributes(): array
    {
        return [
            'role.value' => 'cargo',
        ];
    }

    public function messages(): array
    {
        return [
            'role.value.required' => 'O cargo é obrigatório',
            'role.value.exists' => 'O cargo selecionado é inválido',
        ];
    }
}
