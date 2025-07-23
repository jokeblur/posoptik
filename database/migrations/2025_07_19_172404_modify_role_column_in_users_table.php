<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify ENUM values using raw SQL
            DB::statement("ALTER TABLE users CHANGE COLUMN role role ENUM('super admin', 'admin', 'kasir', 'passet', 'passet bantu') NOT NULL DEFAULT 'passet'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert ENUM values using raw SQL
            DB::statement("ALTER TABLE users CHANGE COLUMN role role ENUM('super admin', 'admin', 'kasir', 'passet') NOT NULL DEFAULT 'passet'");
        });
    }
};
