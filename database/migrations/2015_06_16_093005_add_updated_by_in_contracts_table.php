<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddUpdatedByInContractsTable extends Migration {

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
				$table->integer('updated_by')->nullable();
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
				$table->dropColumn('updated_by');
			}
		);
	}

}
