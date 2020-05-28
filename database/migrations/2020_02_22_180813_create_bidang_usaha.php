<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidangUsaha extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kategori_bidang_usaha', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode', 20);
            $table->string('nama', 255);
        });
        Schema::create('bidang_usaha', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kategori', 20);
            $table->string('kode', 20);
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kategori_bidang_usaha');
        Schema::dropIfExists('bidang_usaha');
    }
}
