<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('dokter_manual')->nullable()->after('id_pasien');
        });
    }
    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('dokter_manual');
        });
    }
};
