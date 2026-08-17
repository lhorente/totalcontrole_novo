<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContatosClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contatos_clientes', function (Blueprint $table) {
            // contatos.id é bigint SIGNED (legado da tabela clientes original do v1),
            // não unsignedBigInteger — precisa bater exatamente para a FK funcionar.
            $table->bigInteger('id_contato')->primary();

            $table->decimal('valor_hora', 12, 2)->nullable();
            $table->string('forma_cobranca')->nullable();
            $table->string('contrato_url')->nullable();
            $table->text('observacoes')->nullable();

            $table->foreign('id_contato')
                  ->references('id')
                  ->on('contatos')
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
        Schema::dropIfExists('contatos_clientes');
    }
}
