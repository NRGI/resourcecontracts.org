<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateContractDiscussionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'contract_discussions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('contract_id')->unsigned();
                $table->foreign('contract_id')
                      ->references('id')->on('contracts')
                      ->onDelete('cascade');
                $table->string('key', 200);
                $table->text('message');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                      ->references('id')->on('users')
                      ->onDelete('cascade');
                $table->enum('type', ['metadata', 'annotation']);
                $table->enum('status', [0, 1])->default(0);
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
        Schema::drop('contract_discussions');
    }

}
