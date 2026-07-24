<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddPdKananKiriToPrescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('prescriptions', 'add_kanan')) {
                $table->string('add_kanan')->nullable()->after('add');
            }
            if (!Schema::hasColumn('prescriptions', 'add_kiri')) {
                $table->string('add_kiri')->nullable()->after('add_kanan');
            }
            if (!Schema::hasColumn('prescriptions', 'pd_kanan')) {
                $table->string('pd_kanan')->nullable()->after('pd');
            }
            if (!Schema::hasColumn('prescriptions', 'pd_kiri')) {
                $table->string('pd_kiri')->nullable()->after('pd_kanan');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'add_kanan')) {
                $table->dropColumn('add_kanan');
            }
            if (Schema::hasColumn('prescriptions', 'add_kiri')) {
                $table->dropColumn('add_kiri');
            }
            if (Schema::hasColumn('prescriptions', 'pd_kanan')) {
                $table->dropColumn('pd_kanan');
            }
            if (Schema::hasColumn('prescriptions', 'pd_kiri')) {
                $table->dropColumn('pd_kiri');
            }
        });
    }
}
