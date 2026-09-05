<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettlementTrackingToPenjualanTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('jumlah_pelunasan', 15, 2)->nullable()->after('kekurangan');
            $table->timestamp('waktu_pelunasan')->nullable()->after('jumlah_pelunasan');
            $table->unsignedBigInteger('pelunasan_by_user_id')->nullable()->after('waktu_pelunasan');
        });
    }

    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_pelunasan', 'waktu_pelunasan', 'pelunasan_by_user_id']);
        });
    }
}