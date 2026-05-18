<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToContractPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a composite index on (contract_id, page_no).
     *
     * contract_pages only declares a foreign key on contract_id. On MySQL a
     * foreign key auto-creates an index on the child column, but PostgreSQL
     * does not — so on Postgres the table had no index usable for the
     * per-page lookup "WHERE contract_id = ? AND page_no = ?", forcing a
     * sequential scan on every page read/update. The composite also serves
     * contract-scoped queries via its leading column.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_pages', function (Blueprint $table) {
            $table->index(['contract_id', 'page_no']);
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
            $table->dropIndex(['contract_id', 'page_no']);
        });
    }
}
