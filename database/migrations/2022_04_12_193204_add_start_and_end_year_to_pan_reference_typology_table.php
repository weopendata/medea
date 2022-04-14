<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartAndEndYearToPanReferenceTypologyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pan_reference_typology', function (Blueprint $table) {
            $table->integer('start_year')->after('uri')->nullable()->index();
            $table->integer('end_year')->after('uri')->nullable()->index();
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
            $table->dropColumn('start_year');
            $table->dropColumn('end_year');
        });
    }
}
