<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionStatusAndAdditionalCostToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('transaction_status')->default('Normal')->after('status');
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->decimal('additional_cost', 10, 2)->default(0)->after('subtotal');
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
            $table->dropColumn('transaction_status');
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropColumn('additional_cost');
        });
    }
}
