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
        Schema::create('anuncios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nome', 60)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('arquivo');
            $table->string('nome_anunciante', 60)->nullable();
            $table->double('valor_anuncio_mensal');
            $table->boolean('ativo')->default(1);
            $table->date('data_comeco_campanha')->nullable();
            $table->date('data_fim_campanha');
            $table->tinyInteger('tipo_campanha');
            $table->string('tel_contato_anunciante', 20)->nullable();
            $table->string('email_contato', 40)->nullable();
            $table->foreign('tenant_id')->references('id')->on('unidades');
            $table->boolean('unidade_pausada')->default(0);

            $table->integer('id_old')->nullable();
            $table->integer('tenant_id_old')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncios');
    }
};
