<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnnotationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_annotations', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('contract_id')->unsigned();
			$table->json('annotation');
			$table->string('url');
			$table->integer('user_id')->unsigned();
			$table->integer('document_page_no')->unsigned()->nullable();
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
		Schema::drop('contract_annotations');
	}

}
