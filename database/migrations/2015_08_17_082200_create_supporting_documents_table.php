<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportingDocumentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'supporting_contracts',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer("contract_id")
                      ->references('id')->on('contracts')
                      ->onDelete('cascade');
                $table->integer('supporting_contract_id')
                      ->references('id')->on('contracts')
                      ->onDelete('cascade');
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
        Schema::drop('supporting_contracts');
    }

}
