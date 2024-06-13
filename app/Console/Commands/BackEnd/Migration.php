<?php

namespace App\Console\Commands\BackEnd;

/**
 * Class Migration
 *
 * This class represents a migration task that generates a column definition
 * or a foreign key constraint in a database schema.
 */
class Migration {

    protected mixed $column;
    protected mixed $type;
    protected mixed $something;
    protected mixed $required;
    protected bool $foreign;

    private const string COLUMN_STR = '$table->%s("%s");';
    private const string COLUMN_SOMETHING_STR = '$table->%s("%s", %s);';
    private const string FOREIGN_STR = '$table->foreign("%s")->references("%s")->on("%s");';

    /**
     * Constructor for the class.
     *
     * @param mixed $column The column value.
     * @param mixed $type The type value.
     * @param mixed $something The something value.
     * @param bool $foreign (Optional) Whether it is a foreign value. Default is false.
     *
     * @return void
     */
    public function __construct(mixed $column, mixed $type, mixed $something, mixed $required, bool $foreign = false)
    {
        $this->column = $column;
        $this->type = $type;
        $this->something = $something;
        $this->required = $required;
        $this->foreign = $foreign;
    }

    /**
     * Generates the column string for the builder.
     *
     * @return string The generated column string.
     */
    public function builderColumn(): string
    {
        $property = '';

        if($this->foreign){
            $property = sprintf(self::FOREIGN_STR, $this->type, $this->column, $this->something);
        } elseif(isset($this->something) && $this->something !== 'null'){
            $property = sprintf(self::COLUMN_SOMETHING_STR, $this->type, $this->column, $this->something);
        } else {
            $property = sprintf(self::COLUMN_STR, $this->type, $this->column);
        }

        if(!$this->required && $property) {
            $property = str_replace(';', "->nullable();", $property);
        }

        return $property;
    }
}
