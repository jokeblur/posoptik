<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisTransaksiToPenjualanTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('jenis_transaksi')->default('Stock')->after('transaction_status');
        });
    }

    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('jenis_transaksi');
        });
    }
}