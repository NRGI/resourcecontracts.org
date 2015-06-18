<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class UpdateStatusInContractsTable
 */
class UpdateStatusInContractsTable extends Migration {

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
				$table->dropColumn('status');
			}
		);

		Schema::table(
			'contracts',
			function (Blueprint $table) {
				$table->enum('status', ['draft', 'completed', 'published', 'rejected'])->default('draft');
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
				$table->dropColumn('status');
			}
		);

		Schema::table(
			'contracts',
			function (Blueprint $table) {
				$table->enum('status', ['draft', 'complete', 'publish'])->default('draft');
			}
		);
	}

}
