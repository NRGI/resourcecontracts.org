<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateActionInContractsComment extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contract_comments',
            function (Blueprint $table) {
                $table->enum('action1', config('nrgi.annotation_stage'))->nullable();
            }
        );
        DB::statement("UPDATE contract_comments SET action1 = action");
        DB::statement("ALTER TABLE contract_comments DROP COLUMN action");
        DB::statement("ALTER TABLE contract_comments RENAME COLUMN action1 TO action;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'contract_comments',
            function (Blueprint $table) {
                $table->dropColumn('action');
            }
        );
    }

}
