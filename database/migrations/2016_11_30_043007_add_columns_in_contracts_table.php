<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnsInContractsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('contracts', function($table) {
            $table->date('published_date')->nullable();;
            $table->integer('published_to_newsletter')->default(0);;
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('contracts', function($table) {
            $table->dropColumn('published_date');
            $table->dropColumn('published_to_newsletter');
        });
	}

}
