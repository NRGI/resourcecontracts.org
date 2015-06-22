<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddStatusInContractsAnnotationTable
 */
class AddStatusInContractsAnnotationTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_annotations', function(Blueprint $table) {
            $table->enum('status', config('nrgi.annotation_stage'))->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_annotations', function(Blueprint $table) {
            $table->dropColumn('status');
        });
    }

}

