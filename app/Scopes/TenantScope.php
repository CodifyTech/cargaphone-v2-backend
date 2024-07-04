<?php

namespace App\Scopes;

use App\Utils\Token;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;


class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $payload = Token::decode();
            if ($payload['role_id'] == 1) {
                return;
            } else {
                $builder->where('tenant_id', $payload['tenant_id']);
            }
        }
    }
}
