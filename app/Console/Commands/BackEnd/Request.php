<?php

namespace App\Console\Commands\BackEnd;

class Request {
    protected mixed $type;
    protected mixed $something;
    protected mixed $required;

    /**
     * Constructor for the class.
     *
     * @param mixed $type The type value.
     * @param mixed $something The something value.
     * @param mixed $required
     */
    public function __construct(mixed $type, mixed $something, mixed $required)
    {
        $this->type = $type;
        $this->something = $something;
        $this->required = $required;
    }

    private function getValidationRule(string $type): string
    {
        return match ($type) {
            'string', 'text' => 'string',
            'integer' => 'integer',
            'bool', 'boolean', 'tinyInteger' => 'boolean',
            'date' => 'date',
            'time' => 'date_format:H:i:s',
            'datetime' => 'date_format:Y-m-d H:i:s',
            'json' => 'json',
            'float', 'double', 'decimal' => 'numeric',
            default => 'string',
        };
    }

    /**
     * Generates the column string for the builder.
     *
     * @return string The generated column string.
     */
    public function builderRequest(): string
    {
        $property = [];

        if(isset($this->type)){
            $property[] = $this->getValidationRule($this->type);
        }
        if(isset($this->something) && $this->something !== null && !empty($this->something)){
            $property[] = "max:$this->something";
        }
        if($this->required){
            $property[] = 'required';
        }

        return implode('|', $property);
    }
}
