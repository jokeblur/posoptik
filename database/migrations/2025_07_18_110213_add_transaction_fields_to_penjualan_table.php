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
            if (!Schema::hasColumn('penjualan', 'pasien_id')) {
                $table->unsignedBigInteger('pasien_id')->nullable()->after('branch_id');
                // $table->foreignId('pasien_id')->nullable()->constrained('pasien')->after('branch_id');
            }
            if (!Schema::hasColumn('penjualan', 'dokter_id')) {
                $table->unsignedBigInteger('dokter_id')->nullable()->after('pasien_id');
                // $table->foreignId('dokter_id')->nullable()->constrained('dokters')->after('pasien_id');
            }
            if (!Schema::hasColumn('penjualan', 'tanggal_siap')) {
                $table->date('tanggal_siap')->nullable()->after('dokter_id');
            }
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
            if (Schema::hasColumn('penjualan', 'pasien_id')) {
                $table->dropForeign(['pasien_id']);
                $table->dropColumn('pasien_id');
            }
            if (Schema::hasColumn('penjualan', 'dokter_id')) {
                $table->dropForeign(['dokter_id']);
                $table->dropColumn('dokter_id');
            }
            if (Schema::hasColumn('penjualan', 'tanggal_siap')) {
                $table->dropColumn('tanggal_siap');
            }
        });
    }
};
