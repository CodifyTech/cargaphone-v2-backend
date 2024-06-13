<?php

namespace Domains\Auth\Requests;

use Domains\Shared\Requests\BaseFormRequest;

class PermissionRequest extends BaseFormRequest
{
    public function base(): array
    {
        return [];
    }

    public function view(): array
    {
        return [];
    }

    public function store(): array
    {
        return ['name' => 'required|string|max:255|unique:'.config('permission.table_names.permissions', 'permissions').',name',];
    }

    public function update(): array
    {
        return ['name' => 'required|string|max:255|unique:'.config('permission.table_names.permissions', 'permissions').',name,'.$this->request->get('id'),];
    }

    public function destroy(): array
    {
        return [];
    }
}
