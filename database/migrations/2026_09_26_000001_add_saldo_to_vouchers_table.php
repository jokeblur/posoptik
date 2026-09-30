<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSaldoToVouchersTable extends Migration
{
    /**
     * Voucher uang kini punya saldo yang berkurang tiap dipakai dan bisa ditambah admin.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('vouchers', 'saldo')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->decimal('saldo', 15, 2)->default(0)->after('nominal');
            });

            // Saldo awal = nominal dikurangi potongan yang sudah terpakai di penjualan.
            $hasVoucherPenjualan = Schema::hasColumn('penjualan', 'voucher_id');
            foreach (DB::table('vouchers')->get() as $voucher) {
                $terpakai = $hasVoucherPenjualan
                    ? (float) DB::table('penjualan')->where('voucher_id', $voucher->id)->sum('voucher_potongan')
                    : 0;
                $saldo = ($voucher->jenis_nominal ?? 'uang') === 'uang'
                    ? max(0, (float) $voucher->nominal - $terpakai)
                    : 0;
                DB::table('vouchers')->where('id', $voucher->id)->update(['saldo' => $saldo]);
            }
        }

        // Voucher uang bisa dipakai berkali-kali, jadi voucher_id di penjualan tidak lagi unik.
        if (Schema::hasColumn('penjualan', 'voucher_id') && $this->indexExists('penjualan', 'penjualan_voucher_id_unique')) {
            Schema::table('penjualan', function (Blueprint $table) {
                $table->index('voucher_id', 'penjualan_voucher_id_index');
            });
            Schema::table('penjualan', function (Blueprint $table) {
                $table->dropUnique('penjualan_voucher_id_unique');
            });
        }

        if (!Schema::hasTable('voucher_saldo_logs')) {
            Schema::create('voucher_saldo_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
                $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('jenis', ['tambah', 'pakai', 'kembali']);
                $table->decimal('jumlah', 15, 2);
                $table->decimal('saldo_sesudah', 15, 2);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['voucher_id', 'created_at']);
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_saldo_logs');

        if (Schema::hasColumn('vouchers', 'saldo')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('saldo');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return !empty(DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]));
    }
}
