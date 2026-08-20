<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bens', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_workspace')->nullable();

            $table->string('tipo');
            $table->string('nome');
            $table->string('detalhe')->nullable();
            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
                  ->nullOnDelete();

            $table->index(['id_workspace', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bens');
    }
}
