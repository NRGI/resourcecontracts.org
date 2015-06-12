<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPdfStructureToContractsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contracts', function(Blueprint $table) {
			$table->enum('pdf_structure', config('nrgi.pdf_structure'))->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contracts', function(Blueprint $table) {
			$table->drop('pdf_structure');
		});
	}

}
