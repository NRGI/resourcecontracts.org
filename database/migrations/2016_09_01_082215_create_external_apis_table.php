<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateExternalApisTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'external_apis',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('site');
                $table->string('url');
                $table->timestamp('last_index_date')->nullable();
                $table->timestamp('created_at')->nullable();
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
        Schema::drop('external_apis');
    }

}
