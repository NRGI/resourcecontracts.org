<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeNullableAndDropDefaultValueFromStatusInContactsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE contracts ALTER COLUMN text_status DROP NOT NULL");
        DB::statement("ALTER TABLE contracts ALTER COLUMN text_status DROP DEFAULT");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE contracts ALTER COLUMN text_status SET NOT NULL");
        DB::statement("ALTER TABLE contracts ALTER COLUMN text_status SET DEFAULT 'draft'::character varying");
    }

}
