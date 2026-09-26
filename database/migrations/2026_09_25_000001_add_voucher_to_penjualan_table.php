<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoucherToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('penjualan') || Schema::hasColumn('penjualan', 'voucher_id')) {
            return;
        }

        Schema::table('penjualan', function (Blueprint $table) {
            // Tidak unik: voucher uang bisa dipakai berkali-kali selama saldonya masih ada.
            $table->foreignId('voucher_id')->nullable()->after('diskon')
                ->constrained('vouchers')->nullOnDelete();
            $table->string('voucher_kode', 100)->nullable()->after('voucher_id');
            $table->decimal('voucher_potongan', 15, 2)->default(0)->after('voucher_kode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('penjualan', 'voucher_id')) {
            return;
        }

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'voucher_kode', 'voucher_potongan']);
        });
    }
}
