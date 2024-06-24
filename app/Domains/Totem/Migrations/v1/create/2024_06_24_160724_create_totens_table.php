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
        Schema::create('totens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("nome", 40);
			$table->string("identificador", 40);
			$table->string("descricao", 100)->nullable();
			$table->string("ip", 100)->nullable();
			$table->string("latitude", 50)->nullable();
			$table->string("longitude", 50)->nullable();
			$table->dateTime("ultima_conexao")->nullable();
			$table->string("conexao_id", 100)->nullable();
			$table->boolean("ativo")->default(1)->nullable();
			$table->uuid("estabelecimento_id")->nullable();
			$table->uuid("tenant_id")->nullable();
            $table->timestamps();

            $table->foreign("estabelecimento_id")->references("id")->on("estabelecimentos");
			$table->foreign("tenant_id")->references("id")->on("unidades");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('totens');
    }
};
