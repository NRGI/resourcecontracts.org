<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProcedureCustomType extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // migrate:fresh drops tables but not custom types, so make this idempotent.
        DB::statement("DROP TYPE IF EXISTS quality CASCADE");
        DB::statement("CREATE TYPE quality AS (company_no integer,concession_no integer,government_no integer)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP TYPE IF EXISTS quality");
    }

}
