<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchKodeToVouchersTable extends Migration
{
    /**
     * Voucher yang dibuat sekaligus (KODE-001, KODE-002, ...) dikelompokkan lewat batch_kode
     * agar bisa dicetak bersama; tiap voucher tetap punya kode unik & sekali pakai.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('vouchers', 'batch_kode')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->string('batch_kode', 100)->nullable()->after('kode')->index();
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('vouchers', 'batch_kode')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropIndex(['batch_kode']);
                $table->dropColumn('batch_kode');
            });
        }
    }
}
