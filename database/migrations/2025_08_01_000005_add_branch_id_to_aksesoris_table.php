<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('aksesoris', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('stok');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::table('aksesoris', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
}; 