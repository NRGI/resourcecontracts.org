<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMturkTaskItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mturk_task_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')
                  ->references('id')->on('mturk_tasks')
                  ->onDelete('cascade');
            $table->integer('page_no')->unsigned();
            $table->text('text')->nullable();
            $table->json('answer')->nullable();
            $table->string('pdf_url', 200);
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
        Schema::drop('mturk_task_items');
    }
}
