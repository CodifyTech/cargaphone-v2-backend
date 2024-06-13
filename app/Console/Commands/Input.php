<?php

namespace App\Console\Commands;

use App\Domains\Auth\Enums\InputEnum;
use Exception;
use Illuminate\Support\Facades\Validator;

class Input
{
    public function __construct()
    {
    }

    /**
     * Renders an input based on the type specified.
     *
     * @return mixed The result of the input handler method.
     * @throws Exception If the type does not have a matching handler.
     *
     */
    public function renderInput($base, string $type, string $message, $field, $rules, array $args = []): mixed
    {
        $inputHandlers = [
            InputEnum::ASK->value => ['default'],
            InputEnum::SECRET->value => ['fallback'],
            InputEnum::CONFIRM->value => ['default'],
            InputEnum::ANTICIPATE->value => ['choices', 'default'],
            InputEnum::CHOICE->value => ['choices', 'default', 'attempts', 'multiple'],
        ];

        if (!isset($inputHandlers[$type])) {
            throw new Exception('Unexpected match value');
        }

        dump($args);

        $argsSpec = $inputHandlers[$type];

        $argsNew = array_fill_keys($argsSpec, null);
        $argsNew = array_replace($argsNew, $argsNew);
        $argsNew = array_values($argsNew);

        $input = $base->$type($message, ...$argsNew);

        if($error = $this->validateInput($rules, $field, $input)) {
            $base->error($error);
            return $this->renderInput($base, $type, $message, $field, $rules, $args);
        }
        return $input;
    }

    public function renderAsk($base, string $question, string $field, array $rules, string $default = null)
    {
        $value = $base->ask($question, $default);

        if($message = $this->validateInput($rules, $field, $value)) {
            $base->error($message);

            return $this->renderAsk($base, $question, $field, $rules, $default);
        }

        return $value;
    }

    public function renderChoice($base, string $question, string $field, array $rules, array $choices, string $default = null)
    {
        $value = $base->choice($question, $choices, $default);

        if($message = $this->validateInput($rules, $field, $value)) {
            $base->error($message);

            return $this->renderChoice($base, $question, $field, $rules, $choices, $default);
        }

        return $value;
    }

    protected function validateInput($rules, $fieldName, $value)
    {
        $validator = Validator::make([
            $fieldName => $value
        ], [
            $fieldName => $rules
        ]);

        return $validator->fails()
            ? $validator->errors()->first($fieldName)
            : null;
    }
}
