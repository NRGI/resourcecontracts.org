<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateContractCommentsTable
 */
class CreateContractCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('contract_id')->unsigned();
			$table->foreign('contract_id')
				  ->references('id')->on('contracts')
				  ->onDelete('cascade');
			$table->text('message');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')
				  ->references('id')->on('users')
				  ->onDelete('cascade');
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
		Schema::drop('contract_comments');
	}

}
