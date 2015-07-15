<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddActionToContractCommentsTable
 */
class AddActionToContractCommentsTable extends Migration
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
                $table->enum('action', config('nrgi.annotation_stage'))->nullable();
            }
        );
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
