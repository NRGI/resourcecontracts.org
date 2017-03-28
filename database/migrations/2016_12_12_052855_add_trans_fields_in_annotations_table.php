<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddTransFieldsInAnnotationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contract_annotations',
            function (Blueprint $table) {
                $table->json('text_trans')->default('{}');
            }
        );

        Schema::table(
            'contract_annotation_pages',
            function (Blueprint $table) {
                $table->json('article_reference_trans')->default('{}');
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
            'contract_annotations',
            function (Blueprint $table) {
                $table->dropColumn('text_trans');
            }
        );

        Schema::table(
            'contract_annotation_pages',
            function (Blueprint $table) {
                $table->dropColumn('article_reference_trans');
            }
        );
    }

}