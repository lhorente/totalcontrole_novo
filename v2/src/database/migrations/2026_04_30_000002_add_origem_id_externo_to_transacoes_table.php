<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrigemIdExternoToTransacoesTable extends Migration
{
    public function up()
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->string('origem', 50)->nullable()->after('chave_banco');
            $table->string('id_externo', 100)->nullable()->after('origem');
            $table->unique(['id_workspace', 'origem', 'id_externo'], 'transacoes_workspace_origem_externo_unique');
        });
    }

    public function down()
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropUnique('transacoes_workspace_origem_externo_unique');
            $table->dropColumn(['origem', 'id_externo']);
        });
    }
}
