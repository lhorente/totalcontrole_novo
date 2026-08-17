<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventosDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventos_documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_evento')->primary();

            $table->string('numero_documento')->nullable();
            $table->string('emissor')->nullable();
            $table->string('arquivo_url')->nullable();
            $table->text('observacoes')->nullable();

            $table->foreign('id_evento')
                  ->references('id')
                  ->on('eventos')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos_documentos');
    }
}
