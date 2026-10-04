<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrcamentosAnuaisTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // "orcamentos" já existe: é o orçamento por categoria/mês do v1 (legado), por isso o sufixo "anual".
        // Um orçamento por workspace e ano: renda prevista mês a mês e a meta (% da renda) de cada grupo
        Schema::create('orcamentos_anuais', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_workspace')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();

            $table->unsignedSmallInteger('ano');
            $table->json('renda');
            $table->json('metas');

            $table->timestamps();

            $table->foreign('id_workspace')
                  ->references('id')
                  ->on('workspaces')
                  ->nullOnDelete();

            $table->unique(['id_workspace', 'ano']);
        });

        // Cada item é uma linha da grade; `valores` guarda o planejado dos 12 meses (a provisão de cada mês),
        // recalculado a partir de valor_fixo / sub-itens sempre que o item é salvo
        Schema::create('orcamento_anual_itens', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_orcamento');
            $table->unsignedBigInteger('id_workspace')->nullable();

            $table->string('grupo');
            $table->string('nome');

            // Sem FK de banco para categorias: a tabela legada usa MyISAM (mesmo motivo de eventos_planejamento.id_transacao)
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->string('padrao_descricao')->nullable();
            $table->string('padrao_normalizado')->nullable();

            $table->string('tipo')->default('fixo');
            $table->decimal('valor_fixo', 12, 2)->nullable();
            $table->string('distribuicao')->nullable();
            $table->json('valores');

            $table->unsignedSmallInteger('ordem')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_orcamento')
                  ->references('id')
                  ->on('orcamentos_anuais')
                  ->cascadeOnDelete();

            $table->index(['id_workspace', 'id_orcamento']);
            $table->index('id_categoria');
        });

        Schema::create('orcamento_anual_subitens', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_item');
            $table->unsignedTinyInteger('mes');
            $table->string('descricao');
            $table->decimal('valor', 12, 2);

            $table->foreign('id_item')
                  ->references('id')
                  ->on('orcamento_anual_itens')
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
        Schema::dropIfExists('orcamento_anual_subitens');
        Schema::dropIfExists('orcamento_anual_itens');
        Schema::dropIfExists('orcamentos_anuais');
    }
}
