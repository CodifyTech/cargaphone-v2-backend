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
        Schema::create('estabelecimentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("nome", 40);
			$table->string("razao_social", 40)->nullable();
			$table->string("documento_legal", 50)->nullable();
			$table->string("cnpj", 18)->nullable();
			$table->enum("segmentacao")->nullable();
			$table->string("responsavel", 60);
			$table->string("email_responsavel", 35);
			$table->string("telefone_responsavel", 15)->nullable();
			$table->string("cep", 10);
			$table->string("endereco", 50);
			$table->integer("numero")->nullable();
			$table->string("cidade", 30);
			$table->string("complemento", 30)->nullable();
			$table->string("estado", 2);
			$table->datetime("data_ativacao", );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimentos');
    }
};
