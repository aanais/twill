<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwillDefaultLinkablesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $twillLinkablesTable = config('twill.linkables_table', 'twill_linkables');

        if (!Schema::hasTable($twillLinkablesTable)) {
            Schema::create($twillLinkablesTable, function (Blueprint $table) {
                $table->{twillIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->{twillIntegerMethod()}('linkable_id')->nullable()->unsigned();
                $table->string('linkable_type')->nullable();
                $table->{twillIntegerMethod()}('linked_id')->nullable()->unsigned();
                $table->string('linked_type')->nullable();
                $table->string('value')->nullable();
                $table->string('target')->nullable();
                $table->string('locale', 7)->default($this->getCurrentLocale())->index();

                $table->index(['linkable_type', 'linkable_id']);
            });
        }
    }

    public function getCurrentLocale()
    {
        return getLocales()[0] ?? config('app.locale');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $twillLinkablesTable = config('twill.linkables_table', 'twill_linkables');

        Schema::dropIfExists($twillLinkablesTable);
    }
}
