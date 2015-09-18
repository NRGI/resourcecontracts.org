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
        DB::statement("CREATE TYPE quality AS (company_no integer,concession_no integer,government_no integer)");
        //DB::statement("CREATE TYPE multipleArray AS (present int[][],missing int[][])");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*DB::statement("DROP type quality");
        DB::statement("DROP type multipleArray");*/
    }

}
