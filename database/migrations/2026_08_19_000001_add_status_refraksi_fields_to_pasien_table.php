<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->string('umur')->nullable()->after('nama_pasien');
            $table->text('anamnesa')->nullable()->after('nohp');
            $table->date('tanggal_periksa')->nullable()->after('anamnesa');
        });
    }

    public function down()
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn(['umur', 'anamnesa', 'tanggal_periksa']);
        });
    }
};
