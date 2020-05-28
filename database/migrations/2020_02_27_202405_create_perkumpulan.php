<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerkumpulan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perkumpulan', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('nama_perkumpulan', 255);
            $table->string('nama_alternatif_1', 255)->nullable();
            $table->string('nama_alternatif_2', 255)->nullable();
            $table->string('nama_alternatif_3', 255)->nullable();
            $table->char('kategori_modal', 1);
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

            $table->text('kegiatan');

            $table->timestamps();
        });
        Schema::create('perkumpulan_pemegang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('perkumpulan_id');
            $table->char('tipe', 1)->default(1);
            $table->string('nama', 50);
            $table->string('kedudukan', 50);
            $table->unsignedBigInteger('hp');
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
        Schema::dropIfExists('perkumpulan');
        Schema::dropIfExists('perkumpulan_pemegang');
    }
}
