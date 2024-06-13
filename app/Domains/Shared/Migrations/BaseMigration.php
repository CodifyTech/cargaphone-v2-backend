<?php

namespace Domains\Shared\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class BaseMigration extends Migration
{
    public function __construct()
    {
        //DB::statement("SET UNIQUE_CHECKS=0;");
        //DB::statement("SET FOREIGN_KEY_CHECKS=0;");
    }

    public function __destruct()
    {
        //DB::statement("SET UNIQUE_CHECKS = 1;");
        //DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

