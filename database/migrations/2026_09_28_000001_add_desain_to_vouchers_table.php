<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDesainToVouchersTable extends Migration
{
    /**
     * Gambar desain voucher (depan & belakang) untuk latar saat cetak.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'desain_depan')) {
                $table->string('desain_depan')->nullable()->after('syarat_ketentuan');
            }
            if (!Schema::hasColumn('vouchers', 'desain_belakang')) {
                $table->string('desain_belakang')->nullable()->after('desain_depan');
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['desain_depan', 'desain_belakang']);
        });
    }
}
