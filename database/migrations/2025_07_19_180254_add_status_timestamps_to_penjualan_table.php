<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->timestamp('waktu_selesai_dikerjakan')->nullable()->after('passet_by_user_id');
            $table->timestamp('waktu_sudah_diambil')->nullable()->after('waktu_selesai_dikerjakan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['waktu_selesai_dikerjakan', 'waktu_sudah_diambil']);
        });
    }
};
