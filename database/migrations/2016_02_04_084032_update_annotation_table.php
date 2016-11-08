<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UpdateAnnotationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 */
	public function up()
	{
		Schema::table('contract_annotations', function(Blueprint $table) {
			$table->text('category')->nullable();
			$table->text('text')->nullable();
		});

        DB::update("update contract_annotations set category = annotation->>'category', text = annotation->>'text'");

		Schema::table('contract_annotations', function(Blueprint $table) {
			$table->dropColumn('annotation');
			$table->dropColumn('document_page_no');
			$table->dropColumn('user_id');
		});

		Artisan::call('nrgi:harmonizeannotation');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contract_annotations', function(Blueprint $table) {
			$table->dropColumn('text');
			$table->dropColumn('category');
		});
	}

}
