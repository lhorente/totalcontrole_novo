<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'document')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('document', 20)
                    ->after('email')
                    ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('users', 'document')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('document');
        });
    }
}
