<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventosPlanejamentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventos_planejamento', function (Blueprint $table) {
            $table->unsignedBigInteger('id_evento')->primary();

            $table->unsignedBigInteger('id_bem')->nullable();
            $table->string('categoria')->nullable();
            $table->string('prioridade')->default('necessidade');

            $table->boolean('recorrente')->default(false);
            $table->unsignedSmallInteger('recorrencia_intervalo')->nullable();
            $table->string('recorrencia_unidade')->nullable();

            $table->date('data_conclusao')->nullable();
            $table->decimal('valor_pago', 12, 2)->nullable();

            // Sem FK de banco para transacoes: a tabela legada usa MyISAM,
            // que não suporta foreign keys (mesmo motivo de eventos.id_cliente).
            $table->unsignedBigInteger('id_transacao')->nullable();

            $table->text('observacoes')->nullable();

            $table->foreign('id_evento')
                  ->references('id')
                  ->on('eventos')
                  ->cascadeOnDelete();

            $table->foreign('id_bem')
                  ->references('id')
                  ->on('bens')
                  ->nullOnDelete();

            $table->index('id_bem');
            $table->index('id_transacao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos_planejamento');
    }
}
