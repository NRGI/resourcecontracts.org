<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateContractTable
 */
class CreateContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'contracts',
            function (Blueprint $table) {
                $table->increments('id');
                $table->json('metadata');
                $table->text('file');
                $table->string('filehash', 100)->unique();
                $table->integer('user_id');
                $table->timestamp('created_datetime');
                $table->timestamp('last_updated_datetime');
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
        Schema::drop('contracts');
    }

}
