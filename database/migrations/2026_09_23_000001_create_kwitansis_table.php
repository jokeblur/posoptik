<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKwitansisTable extends Migration
{
    public function up()
    {
        Schema::create('kwitansis', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 30)->default('umum');
            $table->string('nomor', 100)->nullable();
            $table->string('tempat_tanggal', 150)->nullable();
            $table->string('penerima_dari');
            $table->string('untuk_pembayaran')->nullable();
            $table->string('nama_pembuat')->nullable();
            $table->decimal('harga_frame', 15, 2)->nullable();
            $table->decimal('harga_lensa', 15, 2)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->string('terbilang')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'created_at']);
            $table->index('nomor');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kwitansis');
    }
}
