<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdCategoriaPadraoToCartoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_categoria_padrao')->nullable()->after('ultimos_digitos');

            $table->foreign('id_categoria_padrao')
                  ->references('id')
                  ->on('categorias')
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
            $table->dropForeign(['id_categoria_padrao']);
            $table->dropColumn('id_categoria_padrao');
        });
    }
}
