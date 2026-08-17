<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContatosFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contatos_fornecedores', function (Blueprint $table) {
            // contatos.id é bigint SIGNED (legado da tabela clientes original do v1),
            // não unsignedBigInteger — precisa bater exatamente para a FK funcionar.
            $table->bigInteger('id_contato')->primary();

            $table->string('tipo_servico')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('contato_responsavel')->nullable();
            $table->string('forma_pagamento_preferida')->nullable();
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
        Schema::dropIfExists('contatos_fornecedores');
    }
}
