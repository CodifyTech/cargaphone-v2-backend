<?php

use Domains\Anuncio\Models\Anuncio;
use Domains\Totem\Models\Totem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anuncio_totem', function (Blueprint $table) {
            $table->foreignIdFor(Anuncio::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Totem::class)->nullable()->constrained()->cascadeOnDelete();
            $table->integer('anuncio_id_old')->nullable();
            $table->integer('totem_id_old')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anuncio_totem');
    }
};
