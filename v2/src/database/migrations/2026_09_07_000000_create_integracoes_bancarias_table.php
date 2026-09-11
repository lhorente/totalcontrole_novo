<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegracoesBancariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integracoes_bancarias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_workspace')->nullable();
            $table->unsignedBigInteger('id_cartao')->nullable();

            $table->enum('provider', ['nubank', 'bradesco']);
            $table->string('pluggy_item_id', 100);

            $table->enum('status', ['ativo', 'login_error', 'mfa_pendente', 'erro'])->default('ativo');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('ultimo_erro')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
                  ->nullOnDelete();

            // Sem FK para `cartoes`: essa tabela é legada (MyISAM, herdada do v1) e
            // MySQL não permite FK de uma tabela InnoDB para uma tabela MyISAM.
            // O mesmo problema já existe em `cartoes.id_cartao_pai` (a "FK" ali
            // também não é de fato aplicada pelo MySQL, vira só um índice).
            $table->index('id_cartao');

            $table->unique('pluggy_item_id', 'integracoes_bancarias_pluggy_item_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integracoes_bancarias');
    }
}
