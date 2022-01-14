<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAutoApprovedToTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mturk_tasks', function (Blueprint $table) {
            $table->boolean('is_auto_approved')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mturk_tasks', function (Blueprint $table) {
            //
            $table->dropColumn('is_auto_approved');
        });
    }
}
