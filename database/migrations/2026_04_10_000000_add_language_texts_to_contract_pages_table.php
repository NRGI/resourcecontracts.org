<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanguageTextsToContractPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_pages', function (Blueprint $table) {
            $table->text('text_en')->nullable()->after('text');
            $table->text('text_es')->nullable()->after('text_en');
            $table->text('text_fr')->nullable()->after('text_es');
            $table->boolean('is_translation_valid')->default(true)->after('text_fr');
            $table->enum('translation_status', ['pending', 'in_progress', 'completed', 'failed'])->nullable()->after('is_translation_valid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_pages', function (Blueprint $table) {
            $table->dropColumn(['text_en', 'text_es', 'text_fr', 'is_translation_valid', 'translation_status']);
        });
    }
}
