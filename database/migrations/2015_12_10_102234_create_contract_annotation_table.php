<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateContractAnnotationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_annotation', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('contract_id')->unsigned();
			$table->string('text');
			$table->json('annotation');
			$table->integer('user_id')->unsigned();
			$table->foreign('contract_id')
				  ->references('id')->on('contracts')
				  ->onDelete('cascade')->nullable();

			$table->foreign('user_id')
				  ->references('id')->on('users')
				  ->onDelete('cascade')->nullable();
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
		Schema::drop('contract_annotation');
	}

}
