<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateYayasan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yayasan', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('nama_yayasan', 255);
            $table->string('nama_alternatif_1', 255)->nullable();
            $table->string('nama_alternatif_2', 255)->nullable();
            $table->string('nama_alternatif_3', 255)->nullable();
            $table->char('kategori_modal', 1);
            $table->bigInteger('biaya')->default(0);
            $table->unsignedBigInteger('modal_dasar');

            $table->string('alamat', 255);
            $table->smallInteger('provinsi');
            $table->integer('kotkab');
            $table->bigInteger('kecamatan');
            $table->bigInteger('kelurahan');
            $table->string('kodepos', 10);
            $table->smallInteger('rt');
            $table->smallInteger('rw');
            $table->string('kode_telpon', 10)->nullable();
            $table->string('nomor_telpon', 20)->nullable();
            $table->string('nomor_handphone', 20);
            $table->string('email', 100);
            $table->string('bidang', 150);
            

            $table->timestamps();
        });
        Schema::create('yayasan_bidang', function (Blueprint $table) {
            $table->bigInteger('yayasan_id');
            $table->string('bidang', 255);

            // $table->unique(['yayasan_id', 'bidang']);
        });
        DB::statement('CREATE INDEX bidang_idx ON yayasan_bidang (yayasan_id, bidang(100));');
        Schema::create('yayasan_pemegang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('yayasan_id');
            $table->char('tipe', 1)->default(1);
            $table->string('nama', 50);
            $table->string('kedudukan', 50);
            $table->string('hp', 20);
            $table->string('ktp', 255);
            $table->string('npwp', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yayasan');
        Schema::dropIfExists('yayasan_bidang');
        Schema::dropIfExists('yayasan_pemegang');
    }
}
