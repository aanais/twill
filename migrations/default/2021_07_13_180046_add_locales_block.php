<?php

use A17\Twill\Models\Block;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLocalesBlock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $twillBlocksTable = config('twill.blocks_table', 'twill_blocks');

        if (Schema::hasTable($twillBlocksTable) && !Schema::hasColumn($twillBlocksTable, 'locales')) {
            //Create column
            Schema::table($twillBlocksTable, function (Blueprint $table) {
                $table->longText('locales')
                ->nullable();
            });
            //Init datas on column
            $languages = ["value" => config('translatable.locales')];
            DB::table('blocks')
                ->update([
                    "locales" => json_encode($languages)
            ]);
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

        if (Schema::hasTable($twillBlocksTable) && Schema::hasColumn($twillBlocksTable, 'locales')) {
            Schema::table($twillBlocksTable, function (Blueprint $table) {
                $table->dropColumn('locales');
            });
        }
    }
}
