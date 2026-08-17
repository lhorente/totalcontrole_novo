<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_workspace')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->unsignedBigInteger('id_evento_pai')->nullable();

            $table->string('modulo');
            $table->string('tipo');
            $table->string('titulo');

            // Default explícito para neutralizar o auto ON UPDATE CURRENT_TIMESTAMP
            // que o MySQL aplicaria à primeira coluna timestamp NOT NULL sem
            // default (explicit_defaults_for_timestamp=OFF neste servidor) —
            // sem isso, qualquer UPDATE que não toque data_evento a sobrescreveria.
            $table->timestamp('data_evento')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('data_vencimento')->nullable();

            $table->decimal('valor', 12, 2)->nullable();
            $table->string('status')->default('ativo');
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
                  ->nullOnDelete();

            // Sem FK de banco para clientes: a tabela legada usa MyISAM,
            // que não suporta foreign keys.

            $table->foreign('id_evento_pai')
                  ->references('id')
                  ->on('eventos')
                  ->nullOnDelete();

            $table->index(['modulo', 'tipo']);
            $table->index('data_evento');
            $table->index('data_vencimento');
            $table->index('id_cliente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventos');
    }
}
