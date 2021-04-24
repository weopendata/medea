<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPanReferenceTypologyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pan_reference_typology', function ($table) {
           $table->increments('id');
           $table->string('label', 512);
           $table->string('code', 64)->unique()->index();
           $table->string('parent_code', 64)->index();
           $table->string('uri', 512)->index();
           $table->text('meta');
           $table->unsignedInteger('depth');
           $table->unsignedInteger('parent_id')->index()->nullable();
           $table->timestamps();

           $table->foreign('parent_id')->references('id')->on('pan_reference_typology')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pan_reference_typology');
    }
}
