<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHarga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('harga', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipe', 20);
            $table->char('kategori', 1);
            $table->unsignedInteger('normal')->default(0);
            $table->unsignedInteger('khusus')->default(0);
        });

        DB::unprepared('INSERT INTO harga (tipe, kategori) 
            values 
            ("pt", "1"),
            ("pt", "2"),
            ("pt", "3"),
            ("cv", "1"),
            ("cv", "2"),
            ("cv", "3"),
            ("yayasan", "1"),
            ("yayasan", "2"),
            ("yayasan", "3"),
            ("koperasi", "1"),
            ("koperasi", "2"),
            ("koperasi", "3"),
            ("perkumpulan", "1"),
            ("perkumpulan", "2"),
            ("perkumpulan", "3"),
            ("firma", "1"),
            ("firma", "2"),
            ("firma", "3")
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('harga');
    }
}
