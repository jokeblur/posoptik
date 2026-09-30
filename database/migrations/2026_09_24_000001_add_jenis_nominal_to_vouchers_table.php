<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisNominalToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        if (!Schema::hasColumn('vouchers', 'jenis_nominal')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->enum('jenis_nominal', ['uang', 'diskon'])->default('uang')->after('kode');
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
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'jenis_nominal')) {
                $table->dropColumn('jenis_nominal');
            }
        });
    }
}
