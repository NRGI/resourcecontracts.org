<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddStatusInContractsTable
 */
class AddStatusInContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contracts',
            function (Blueprint $table) {
                $table->enum('metadata_status', ['draft', 'completed', 'published', 'rejected'])->default('draft');
                $table->enum('text_status', ['draft', 'completed', 'published', 'rejected'])->default('draft');
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
        Schema::table(
            'contracts',
            function (Blueprint $table) {
                $table->dropColumn('metadata_status');
                $table->dropColumn('text_status');
            }
        );
    }
}
