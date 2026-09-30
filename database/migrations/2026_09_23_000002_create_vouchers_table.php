<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vouchers')) {
            if (!Schema::hasColumn('vouchers', 'jenis_nominal')) {
                Schema::table('vouchers', function (Blueprint $table) {
                    $table->enum('jenis_nominal', ['uang', 'diskon'])->default('uang')->after('kode');
                });
            }
            return;
        }

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 100)->unique();
            $table->enum('jenis_nominal', ['uang', 'diskon'])->default('uang');
            $table->decimal('nominal', 15, 2);
            $table->text('syarat_ketentuan')->nullable();
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['aktif', 'berlaku_sampai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
