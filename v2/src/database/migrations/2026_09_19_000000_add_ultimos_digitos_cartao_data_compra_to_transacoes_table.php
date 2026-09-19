<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUltimosDigitosCartaoDataCompraToTransacoesTable extends Migration
{
    public function up()
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->string('ultimos_digitos_cartao', 4)->nullable()->after('id_cartao');
            $table->date('data_compra')->nullable()->after('data');
        });
    }

    public function down()
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropColumn(['ultimos_digitos_cartao', 'data_compra']);
        });
    }
}
