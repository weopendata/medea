<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadFileTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_files', function ($table) {
           $table->increments('id');
           $table->string('path', 256);
           $table->string('name', 256);
           $table->string('user_name', 256);
           $table->timestamp('last_imported')->nullable();
           $table->timestamps();
        });

        Schema::create('import_jobs', function ($table) {
           $table->increments('id');
           $table->unsignedInteger('import_files_id');
           $table->string('status', 256);
           $table->timestamps();

           $table->foreign('import_files_id')->references('id')->on('import_files')->onDelete('CASCADE');
        });

        Schema::create('import_logs', function ($table) {
           $table->increments('id');
           $table->unsignedInteger('import_jobs_id');
           $table->string('level');
           $table->text('message')->nullable();
           $table->unsignedInteger('line_number');
           $table->string('action');
           $table->text('context')->nullable();
           $table->timestamps();

           $table->foreign('import_jobs_id')->references('id')->on('import_jobs')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('import_logs');
        Schema::drop('import_jobs');
        Schema::drop('import_files');
    }
}
