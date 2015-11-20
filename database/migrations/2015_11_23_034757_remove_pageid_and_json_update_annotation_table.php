<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Nrgi\Entities\Contract\Annotation;

/**
 * Class RemovePageidAndJsonUpdateAnnotationTable
 */
class RemovePageidAndJsonUpdateAnnotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $annotations = Annotation::all();
        foreach ($annotations as $annotation) {
            $annotationArray        = json_decode(json_encode($annotation->annotation), true);
            $removeKeys             = ['document_page_no', 'tags', 'id', 'page', 'url'];
            $annotationArray        = array_diff_key($annotationArray, array_flip($removeKeys));
            $annotation->annotation = $annotationArray;
            $annotation->save();
        }
        Schema::table(
            'contract_annotations',
            function (Blueprint $table) {
                $table->dropColumn('page_id');
                $table->dropColumn('url');
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
                $table->string('url');
                $table->integer('page_id')->unsigned();
                $table->foreign('page_id')
                      ->references('id')->on('contract_pages')
                      ->onDelete('cascade');
            }
        );
    }

}
