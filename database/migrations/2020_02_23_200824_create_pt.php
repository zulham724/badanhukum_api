<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pt', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->string('nama_perusahaan', 255);
            $table->string('nama_alternatif_1', 255)->nullable();
            $table->string('nama_alternatif_2', 255)->nullable();
            $table->string('nama_alternatif_3', 255)->nullable();
            $table->char('kategori_modal', 1);
            $table->unsignedBigInteger('modal_dasar');
            $table->unsignedBigInteger('modal_ditempatkan');
            // $table->unsignedBigInteger('saham_direktur_utama');
            // $table->unsignedBigInteger('saham_komisaris_utama');
            $table->string('alamat', 255);
            $table->smallInteger('provinsi');
            $table->string('kotkab', 255);
            $table->string('kecamatan', 255);
            $table->string('kelurahan', 255);
            $table->string('kodepos', 10);
            $table->smallInteger('rt');
            $table->smallInteger('rw');
            $table->string('kode_telpon', 10)->nullable();
            $table->string('nomor_telpon', 20)->nullable();
            $table->string('nomor_handphone', 20);
            $table->string('email', 100);
            // $table->string('direktur_utama', 255);
            // $table->string('komisaris_utama', 255);

            $table->timestamps();
        });
        Schema::create('pt_bidang', function (Blueprint $table) {
            $table->bigInteger('pt_id');
            $table->string('bidang', 50);
            
            $table->unique(['pt_id', 'bidang']);
        });
        Schema::create('pt_pemegang', function (Blueprint $table) {
            $table->bigInteger('pt_id');
            $table->char('tipe', 1)->default(1);
            $table->string('nama', 50);
            $table->string('kedudukan', 50);
            $table->unsignedBigInteger('saham');
            $table->string('ktp', 255);
            $table->string('npwp', 255)->nullable();

            $table->unique(['pt_id', 'tipe', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pt');
        Schema::dropIfExists('pt_bidang');
        Schema::dropIfExists('pt_pemegang');
    }
}
