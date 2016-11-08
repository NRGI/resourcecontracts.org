<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddMturkStatusInContracts extends Migration {

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
				$table->enum('mturk_status', [1, 2])->nullable();  // 1: sent to mturk system 2:tasks complete
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
				$table->dropColumn('mturk_status');
			}
		);
	}

}
