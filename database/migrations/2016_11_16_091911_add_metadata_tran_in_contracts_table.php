<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddMetadataTranInContractsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contracts',
            function (Blueprint $table) {
                $table->json('metadata_trans')->default('{}');
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
            'contracts',
            function (Blueprint $table) {
                $table->dropColumn('metadata_trans');
            }
        );
    }

}
