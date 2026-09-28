<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVoucherDanaTable extends Migration
{
    /**
     * Dompet/anggaran voucher: satu dana induk yang diisi admin. Saldo voucher uang
     * (saldo awal & tambah saldo) diambil dari dana ini, dan sisa saldo voucher yang
     * dihapus dikembalikan ke dana.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('voucher_dana')) {
            Schema::create('voucher_dana', function (Blueprint $table) {
                $table->id();
                $table->decimal('saldo', 15, 2)->default(0);
                $table->timestamps();
            });

            DB::table('voucher_dana')->insert([
                'saldo' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('voucher_dana_logs')) {
            Schema::create('voucher_dana_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
                $table->string('voucher_kode', 100)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('jenis', ['isi', 'tarik', 'ambil', 'kembali']);
                $table->decimal('jumlah', 15, 2);
                $table->decimal('saldo_sesudah', 15, 2);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_dana_logs');
        Schema::dropIfExists('voucher_dana');
    }
}
