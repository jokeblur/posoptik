<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialFieldsToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('diskon', 15, 2)->default(0)->after('total');
            $table->decimal('bayar', 15, 2)->default(0)->after('diskon');
            $table->decimal('kekurangan', 15, 2)->default(0)->after('bayar');
            $table->enum('status', ['Lunas', 'Belum Lunas'])->default('Belum Lunas')->after('kekurangan');
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
            $table->dropColumn(['diskon', 'bayar', 'kekurangan', 'status']);
        });
    }
}
