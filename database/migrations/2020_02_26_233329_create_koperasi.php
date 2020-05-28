<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKoperasi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('koperasi', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('nama_koperasi', 255);
            $table->string('nama_alternatif_1', 255)->nullable();
            $table->string('nama_alternatif_2', 255)->nullable();
            $table->string('nama_alternatif_3', 255)->nullable();
            $table->char('wilayah', 1);
            $table->char('jenis', 1);
            $table->char('kategori_modal', 1);
            $table->unsignedBigInteger('modal_dasar');
            $table->bigInteger('wajib');
            $table->bigInteger('pokok');
            $table->bigInteger('sukarela');

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

            $table->string('unit_simpan_pinjam', 5);
            $table->bigInteger('alokasi');
            $table->smallInteger('jumlah_anggota');

            $table->string('ktp_anggota', 255);
            $table->string('daftar_hadir', 255);
            $table->string('rekapitulasi', 255);
            $table->string('berita_pendirian', 255);

            $table->timestamps();
        });
        Schema::create('koperasi_bidang', function (Blueprint $table) {
            $table->bigInteger('koperasi_id');
            $table->string('bidang', 50);

            $table->unique(['koperasi_id', 'bidang']);
        });
        Schema::create('koperasi_pemegang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('koperasi_id');
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
        Schema::dropIfExists('koperasi');
        Schema::dropIfExists('koperasi_bidang');
        Schema::dropIfExists('koperasi_pemegang');
    }
}
