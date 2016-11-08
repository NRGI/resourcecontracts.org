<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractPagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_pages', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('contract_id')->unsigned();
			$table->foreign('contract_id')
				  ->references('id')->on('contracts')
				  ->onDelete('cascade');
			$table->integer('page_no')->unsigned();
			$table->text('text');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_pages');
	}

}
