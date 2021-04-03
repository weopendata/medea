<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContextToImportJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->text('context')->nullable();
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->text('status')->after('context');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropColumn('context');
        });

        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
