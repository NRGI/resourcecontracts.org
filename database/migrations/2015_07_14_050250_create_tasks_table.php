<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'mturk_tasks',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('contract_id')->unsigned();
                $table->foreign('contract_id')
                      ->references('id')->on('contracts')
                      ->onDelete('cascade');
                $table->integer('page_no')->unsigned();
                $table->text('text')->nullable();
                $table->string('hit_id')->nullable();
                $table->string('hit_type_id')->nullable();
                $table->string('pdf_url', 200);
                $table->enum('status', [0, 1])->default(0); // pending , completed
                $table->enum('approved', [0, 1, 2])->default(0); // pending approval , approved, rejected
                $table->json('assignments')->nullable();
                $table->timestamps();
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
        Schema::drop('mturk_tasks');
    }

}
