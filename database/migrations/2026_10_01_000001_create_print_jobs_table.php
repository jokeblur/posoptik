<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintJobsTable extends Migration
{
    /**
     * Antrian print: tablet mengirim nota ke antrian, halaman Print Agent di PC cabang
     * mengambil job lalu mencetaknya ke printer yang tersambung di PC.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('print_jobs')) {
            return;
        }

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->cascadeOnDelete();
            $table->string('jenis', 20);
            $table->string('judul', 150);
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'gagal'])->default('menunggu');
            $table->string('pesan', 255)->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dicetak_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diambil_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status', 'id']);
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_jobs');
    }
}
