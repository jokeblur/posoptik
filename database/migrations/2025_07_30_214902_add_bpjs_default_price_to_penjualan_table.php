<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBpjsDefaultPriceToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('pasien_service_type')->nullable()->after('pasien_id');
            $table->decimal('bpjs_default_price', 10, 2)->default(0)->after('transaction_status');
            $table->decimal('total_additional_cost', 10, 2)->default(0)->after('bpjs_default_price');
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
            $table->dropColumn(['pasien_service_type', 'bpjs_default_price', 'total_additional_cost']);
        });
    }
}
