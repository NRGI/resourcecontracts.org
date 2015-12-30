<?php

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Annotation\Page\Page;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAnnotationPagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'contract_annotation_pages',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('annotation_id')->unsigned();
                $table->foreign('annotation_id')
                      ->references('id')->on('contract_annotations')
                      ->onDelete('cascade');
                $table->integer('page_no')->nullable();
                $table->integer('user_id')->unsigned();
                $table->json('annotation');
                $table->text('article_reference');
                $table->timestamps();
            }
        );

        $annotations = Annotation::all();
        foreach ($annotations as $annotation) {
            $json              = json_decode($annotation->annotation, true);
            $article_reference = $json['section'];
            unset($json['category'], $json['text'], $json['cluster'], $json['parent'], $json['section']);
            $page = [
                'annotation_id'     => $annotation->id,
                'user_id'           => $annotation->user_id,
                'page_no'           => $annotation->document_page_no,
                'annotation'        => $json,
                'article_reference' => $article_reference,
                'created_at'        => $annotation->created_at,
                'updated_at'        => $annotation->updated_at
            ];
            $page = Page::create($page);
            echo $page->id;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_annotation_pages');
    }

}
