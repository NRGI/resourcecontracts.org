<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHitDescriptionToTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mturk_tasks', function (Blueprint $table) {
            //
            $table->string('hit_description')->nullable();
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
            $table->dropColumn('hit_description');
        });
    }
}
