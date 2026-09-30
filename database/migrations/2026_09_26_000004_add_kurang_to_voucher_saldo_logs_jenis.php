<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddKurangToVoucherSaldoLogsJenis extends Migration
{
    /**
     * Saldo voucher bisa diedit admin; pengurangan dicatat sebagai jenis 'kurang'.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('voucher_saldo_logs')) {
            DB::statement("ALTER TABLE `voucher_saldo_logs` MODIFY `jenis` ENUM('tambah', 'pakai', 'kembali', 'kurang') NOT NULL");
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('voucher_saldo_logs')) {
            DB::table('voucher_saldo_logs')->where('jenis', 'kurang')->update(['jenis' => 'pakai']);
            DB::statement("ALTER TABLE `voucher_saldo_logs` MODIFY `jenis` ENUM('tambah', 'pakai', 'kembali') NOT NULL");
        }
    }
}
