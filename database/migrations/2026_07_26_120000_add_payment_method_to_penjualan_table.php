<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (!Schema::hasColumn('penjualan', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->nullable()->after('bayar');
            }

            if (!Schema::hasColumn('penjualan', 'bank_transfer')) {
                $table->string('bank_transfer')->nullable()->after('metode_pembayaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan', 'bank_transfer')) {
                $table->dropColumn('bank_transfer');
            }

            if (Schema::hasColumn('penjualan', 'metode_pembayaran')) {
                $table->dropColumn('metode_pembayaran');
            }
        });
    }
};
