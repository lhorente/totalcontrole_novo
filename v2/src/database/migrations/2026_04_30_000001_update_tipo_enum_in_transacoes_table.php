<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTipoEnumInTransacoesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE transacoes MODIFY COLUMN tipo ENUM('despesa','lucro','transferencia','investimento','emprestimo','pagamento_emprestimo','venda') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE transacoes MODIFY COLUMN tipo ENUM('despesa','lucro','transferencia','investimento','emprestimo','pagamento_emprestimo') NOT NULL");
    }
}
