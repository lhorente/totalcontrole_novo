<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWorkspaceAndFieldsToContatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contatos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_workspace')->nullable()->after('id');
            $table->string('tipo')->nullable()->after('nome');
            $table->string('documento')->nullable()->after('status');
            $table->string('email')->nullable()->after('documento');
            $table->string('telefone')->nullable()->after('email');
            $table->text('observacoes')->nullable()->after('telefone');

            $table->foreign('id_workspace')->references('id')->on('workspaces')->nullOnDelete();
        });

        // Backfill: cada contato existente herda o workspace pessoal ativo do
        // usuário que o criou — mesma regra usada por SetActiveWorkspace.
        DB::statement(<<<'SQL'
            UPDATE contatos c
            JOIN workspace_users wu ON wu.user_id = c.id_usuario
            JOIN workspaces w ON w.id = wu.workspace_id AND w.tipo = 'pessoal' AND w.ativo = 1
            SET c.id_workspace = w.id
            WHERE c.id_workspace IS NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contatos', function (Blueprint $table) {
            $table->dropForeign(['id_workspace']);
            $table->dropColumn(['id_workspace', 'tipo', 'documento', 'email', 'telefone', 'observacoes']);
        });
    }
}
