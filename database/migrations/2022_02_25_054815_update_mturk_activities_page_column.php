<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMturkActivitiesPageColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mturk_activities', function (Blueprint $table) {
            DB::statement('ALTER TABLE mturk_activities RENAME COLUMN page_no TO pages');
            DB::statement('ALTER TABLE mturk_activities ALTER COLUMN pages TYPE TEXT');
            DB::statement('ALTER TABLE mturk_activities ALTER COLUMN pages DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mturk_activities', function (Blueprint $table) {
            //
            DB::statement('ALTER TABLE mturk_activities ALTER COLUMN pages TYPE INT USING (pages::integer)');
            DB::statement('ALTER TABLE mturk_activities ALTER COLUMN pages DROP NOT NULL');
            DB::statement('ALTER TABLE mturk_activities RENAME COLUMN pages TO page_no');

            
        });
    }
}
