<?php

namespace Domains\Shared\Rules;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator {
    public function validateConditionalExist($attribute, $value, $parameters, BaseValidator $validator)
    {
        $validator->addReplacer('conditional_exist', function ($message, $attribute, $rule, $parameters, $validator) {
            return __("O registro não existe.");
        });

        return DB::table($parameters[0])->where($parameters[1], $parameters[2], $parameters[3])->exists();
    }
}
