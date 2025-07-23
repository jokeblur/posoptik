<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->id();
            $table->string('kode_frame')->unique();
            $table->string('merk_frame')->nullable();
            $table->string('jenis_frame')->nullable();
            $table->unsignedBigInteger('id_sales')->nullable();
            $table->integer('harga_beli_frame')->nullable();
            $table->integer('harga_jual_frame')->nullable();
            $table->integer('stok')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
        Schema::dropIfExists('frames');
    }
}
