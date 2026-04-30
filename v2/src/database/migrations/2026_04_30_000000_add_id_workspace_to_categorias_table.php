<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdWorkspaceToCategoriasTable extends Migration
{
    public function up()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->unsignedBigInteger('id_workspace')->nullable()->after('id');
            $table->foreign('id_workspace')->references('id')->on('workspaces')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['id_workspace']);
            $table->dropColumn('id_workspace');
        });
    }
}
