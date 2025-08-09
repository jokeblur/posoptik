<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('dokter_id')->nullable()->after('id_pasien');
            $table->string('dokter_manual')->nullable()->after('dokter_id');
        });
    }
    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('dokter_id');
            $table->dropColumn('dokter_manual');
        });
    }
}; 