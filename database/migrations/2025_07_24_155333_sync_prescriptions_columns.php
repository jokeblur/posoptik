<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'dokter_id')) {
                $table->unsignedBigInteger('dokter_id')->nullable()->after('id_pasien');
    }
            if (!Schema::hasColumn('prescriptions', 'dokter_manual')) {
                $table->string('dokter_manual')->nullable()->after('dokter_id');
            }
        });
    }
    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'dokter_manual')) {
                $table->dropColumn('dokter_manual');
            }
            if (Schema::hasColumn('prescriptions', 'dokter_id')) {
                $table->dropColumn('dokter_id');
            }
        });
    }
};
