<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeParaTransacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('de_para_transacoes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_workspace')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_cliente')->nullable();

            $table->string('padrao');
            $table->string('padrao_normalizado');
            $table->string('descricao_local');

            $table->enum('origem', ['manual', 'automatico'])->default('manual');
            $table->unsignedInteger('ocorrencias')->default(0);
            $table->timestamp('ultima_utilizacao')->nullable();
            $table->boolean('ativo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
                  ->nullOnDelete();

            $table->unique(['id_workspace', 'padrao_normalizado'], 'de_para_transacoes_workspace_padrao_unique');
            $table->index('padrao_normalizado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('de_para_transacoes');
    }
}
