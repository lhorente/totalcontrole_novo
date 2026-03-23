<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeTipoEnumInTransacoesTable extends Migration
{
    private array $enumValues = [
        'despesa',
        'lucro',
        'transferencia',
        'investimento',
        'emprestimo',
        'pagamento_emprestimo',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $enumList = implode("','", $this->enumValues);

        DB::statement("ALTER TABLE transacoes MODIFY COLUMN tipo ENUM('{$enumList}') NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE transacoes MODIFY COLUMN tipo VARCHAR(255) NULL");
    }
}
