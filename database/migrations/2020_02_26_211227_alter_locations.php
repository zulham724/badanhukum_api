<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pt_pemegang', function (Blueprint $table) {
            $table->dropUnique(['pt_id', 'tipe', 'nama']);
            $table->bigIncrements('id')->first();
        });
        Schema::create('regencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('province_id');
            $table->string('name', 100);
            $table->string('alt_name', 255)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
        });
        Schema::create('districts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('regency_id');
            $table->string('name', 100);
            $table->string('alt_name', 255)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
        });
        Schema::create('urbans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('district_id');
            $table->string('name', 100);
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('urbans');
        Schema::table('pt_pemegang', function (Blueprint $table) {
            $table->unique(['pt_id', 'tipe', 'nama']);
            $table->dropColumn('id');
        });
    }
}
