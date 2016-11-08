<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageIdInAnnotationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_annotations', function(Blueprint $table) {
			$table->integer('page_id')->unsigned();
			$table->foreign('page_id')
				  ->references('id')->on('contract_pages')
				  ->onDelete('cascade');
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
			$table->dropForeign('contract_annotations_page_id_foreign');
		});
	}

}
