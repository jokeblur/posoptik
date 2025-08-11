<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignatureToPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->text('signature_bpjs')->nullable()->after('photo_bpjs')->comment('Tanda tangan pasien BPJS dalam format base64');
            $table->timestamp('signature_date')->nullable()->after('signature_bpjs')->comment('Waktu tanda tangan dibuat');
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
            $table->dropColumn(['signature_bpjs', 'signature_date']);
        });
    }
}
