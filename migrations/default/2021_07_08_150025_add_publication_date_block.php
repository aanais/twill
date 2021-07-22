<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublicationDateBlock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $twillBlocksTable = config('twill.blocks_table', 'twill_blocks');

        if (Schema::hasTable($twillBlocksTable) && !Schema::hasColumn($twillBlocksTable, 'publication')) {
            Schema::table($twillBlocksTable, function (Blueprint $table) {
                $table->string('publication')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $twillBlocksTable = config('twill.blocks_table', 'twill_blocks');

        if (Schema::hasTable($twillBlocksTable) && Schema::hasColumn($twillBlocksTable, 'publication')) {
            Schema::table($twillBlocksTable, function (Blueprint $table) {
                $table->dropColumn('publication');
            });
        }
    }
}
