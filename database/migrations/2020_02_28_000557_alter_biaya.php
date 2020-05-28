<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBiaya extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pt', function (Blueprint $table) {
            $table->bigInteger('biaya')->default(0)->after('kategori_modal');
        });
        Schema::table('cv', function (Blueprint $table) {
            $table->bigInteger('biaya')->default(0)->after('kategori_modal');
        });
        Schema::table('koperasi', function (Blueprint $table) {
            $table->bigInteger('biaya')->default(0)->after('kategori_modal');
        });
        Schema::table('firma', function (Blueprint $table) {
            $table->bigInteger('biaya')->default(0)->after('kategori_modal');
        });
        Schema::table('perkumpulan', function (Blueprint $table) {
            $table->bigInteger('biaya')->default(0)->after('kategori_modal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pt', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
        Schema::table('cv', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
        Schema::table('koperasi', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
        Schema::table('firma', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
        Schema::table('perkumpulan', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
    }
}
