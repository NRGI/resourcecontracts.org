<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddProcessStatusInContractsTable extends Migration
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
                $table->enum('pdf_process_status', [0, 1, 2, 3])->default(0);
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
                $table->dropColumn('pdf_process_status');
            }
        );
    }

}
