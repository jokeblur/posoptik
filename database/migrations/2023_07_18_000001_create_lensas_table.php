<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLensasTable extends Migration
{
    public function up()
    {
        Schema::create('lensa', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lensa');
            $table->string('merk_lensa');
            $table->string('type')->nullable();
            $table->string('index')->nullable();
            $table->string('coating')->nullable();
            $table->decimal('harga_beli_lensa', 15, 2)->default(0);
            $table->decimal('harga_jual_lensa', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lensa');
    }
}
