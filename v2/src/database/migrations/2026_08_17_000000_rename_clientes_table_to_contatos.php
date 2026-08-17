<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameClientesTableToContatos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('clientes', 'contatos');

        // A tabela legada era MyISAM (por isso nunca teve FK). Convertendo para
        // InnoDB agora, as novas tabelas auxiliares de contatos podem ter FK de
        // verdade (cascadeOnDelete), igual o restante do app.
        DB::statement('ALTER TABLE contatos ENGINE=InnoDB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('contatos', 'clientes');
    }
}
