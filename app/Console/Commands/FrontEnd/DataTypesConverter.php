<?php

namespace App\Console\Commands\FrontEnd;

class DataTypesConverter
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function phpToTsDataType($phpType): string
    {
        return match ($phpType){
            'bool' => 'boolean',
            'float', 'int', 'double' => 'number',
            'string', 'text' => 'string',
            'array' => '[]',
            'object' => '{}',
            default => 'any'
        };
    }

    public static function phpToTsDataTypeValue($phpType): string
    {
        return match ($phpType){
            'bool' => 'false',
            'float', 'int', 'double' => '0',
            'string', 'text' => "''",
            'array' => '[]',
            'object' => '{}',
            default => 'null'
        };
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->phpToTsDataType($this->type);
    }
}
