<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubcartaoFieldsToCartoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cartao_pai')->nullable()->after('id');
            $table->string('ultimos_digitos', 4)->nullable()->after('id_cartao_pai');

            $table->foreign('id_cartao_pai')
                  ->references('id')
                  ->on('cartoes')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartoes', function (Blueprint $table) {
            $table->dropForeign(['id_cartao_pai']);
            $table->dropColumn(['id_cartao_pai', 'ultimos_digitos']);
        });
    }
}
