<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyAnnotationTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_annotations', function(Blueprint $table) {
			$table->foreign('contract_id')
				  ->references('id')->on('contracts')
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
			$table->dropForeign('contract_annotations_contract_id_foreign');
		});
	}
}
