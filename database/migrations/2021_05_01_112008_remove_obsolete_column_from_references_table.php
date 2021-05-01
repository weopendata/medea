<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveObsoleteColumnFromReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pan_reference_typology', function (Blueprint $table) {
            $table->dropColumn('parent_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pan_reference_typology', function (Blueprint $table) {
            $table->string('parent_code', 64)->index();
        });
    }
}
