<?php

use Domains\Shared\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("nome", 60);
			$table->string("cnpj_empresa", 20)->nullable();
			$table->string("email", 30)->nullable()->unique();
			$table->string("nome_responsavel", 60);
			$table->unsignedBigInteger("vindi_costumer_id")->nullable();
			$table->date("dt_abertura")->nullable();
			$table->boolean("ativo")->default('1')->nullable();
			$table->string("nome_rua", 50)->nullable();
			$table->integer("numero")->nullable();
			$table->string("cep", 10)->nullable();
			$table->string("cidade", 30)->nullable();
			$table->string("estado", 2)->nullable();
			$table->timestamp("softDeletes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
