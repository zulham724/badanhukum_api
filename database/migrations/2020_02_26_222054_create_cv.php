<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pt', function (Blueprint $table) {
            $table->integer('kotkab')->change();
            $table->bigInteger('kecamatan')->change();
            $table->bigInteger('kelurahan')->change();
        });
        Schema::create('cv', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('nama_perusahaan', 255);
            $table->string('nama_alternatif_1', 255)->nullable();
            $table->string('nama_alternatif_2', 255)->nullable();
            $table->string('nama_alternatif_3', 255)->nullable();
            $table->char('kategori_modal', 1);
            $table->unsignedBigInteger('modal_dasar');
            $table->unsignedBigInteger('modal_ditempatkan');
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

            $table->timestamps();
        });
        Schema::create('cv_bidang', function (Blueprint $table) {
            $table->bigInteger('cv_id');
            $table->string('bidang', 50);

            $table->unique(['cv_id', 'bidang']);
        });
        Schema::create('cv_pemegang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cv_id');
            $table->char('tipe', 1)->default(1);
            $table->string('nama', 50);
            $table->string('kedudukan', 50);
            $table->unsignedBigInteger('saham');
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
        Schema::dropIfExists('cv');
        Schema::dropIfExists('cv_bidang');
        Schema::dropIfExists('cv_pemegang');
        Schema::table('pt', function (Blueprint $table) {
            $table->string('kotkab', 255)->change();
            $table->string('kecamatan', 255)->change();
            $table->string('kelurahan', 255)->change();
        });
    }
}
