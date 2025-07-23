<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_pasien')->constrained('pasien')->onDelete('cascade');
            $table->unsignedBigInteger('id_pasien');
            $table->string('od_sph')->nullable();
            $table->string('od_cyl')->nullable();
            $table->string('od_axis')->nullable();
            $table->string('os_sph')->nullable();
            $table->string('os_cyl')->nullable();
            $table->string('os_axis')->nullable();
            $table->string('add')->nullable();
            $table->string('pd')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescriptions');
    }
};
