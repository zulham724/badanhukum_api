<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPerkumpulanHp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perkumpulan_pemegang', function (Blueprint $table) {
            $table->string('hp', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('perkumpulan_pemegang', function (Blueprint $table) {
            $table->bigInteger('hp')->change();
        });
    }
}
