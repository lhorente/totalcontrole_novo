<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdWorkspaceToTransacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_workspace')->nullable()->after('id');

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
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
        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropForeign(['id_workspace']);
            $table->dropColumn('id_workspace');
        });
    }
}
