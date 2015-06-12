<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTextTypeInContractTable extends Migration
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
                $table->tinyInteger('textType')->nullable();
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
                $table->dropColumn('textType');
            }
        );
    }

}
