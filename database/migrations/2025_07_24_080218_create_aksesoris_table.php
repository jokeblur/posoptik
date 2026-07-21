<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAksesorisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('aksesoris')) {
            Schema::create('aksesoris', function (Blueprint $table) {
                $table->id();
                $table->string('nama_produk');
                $table->integer('harga_beli');
                $table->integer('harga_jual');
                $table->integer('stok');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aksesoris');
    }
}
