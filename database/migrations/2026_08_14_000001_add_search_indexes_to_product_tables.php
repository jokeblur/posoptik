<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->index(['branch_id', 'merk_lensa'], 'lensa_branch_merk_index');
            $table->index(['branch_id', 'kode_lensa'], 'lensa_branch_kode_index');
            $table->index(['merk_lensa'], 'lensa_merk_index');
            $table->index(['kode_lensa'], 'lensa_kode_index');
        });

        Schema::table('frames', function (Blueprint $table) {
            $table->index(['branch_id', 'merk_frame'], 'frames_branch_merk_index');
            $table->index(['branch_id', 'kode_frame'], 'frames_branch_kode_index');
            $table->index(['merk_frame'], 'frames_merk_index');
            $table->index(['kode_frame'], 'frames_kode_index');
        });

        Schema::table('aksesoris', function (Blueprint $table) {
            $table->index(['branch_id', 'nama_produk'], 'aksesoris_branch_name_index');
            $table->index(['nama_produk'], 'aksesoris_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lensa', function (Blueprint $table) {
            $table->dropIndex('lensa_branch_merk_index');
            $table->dropIndex('lensa_branch_kode_index');
            $table->dropIndex('lensa_merk_index');
            $table->dropIndex('lensa_kode_index');
        });

        Schema::table('frames', function (Blueprint $table) {
            $table->dropIndex('frames_branch_merk_index');
            $table->dropIndex('frames_branch_kode_index');
            $table->dropIndex('frames_merk_index');
            $table->dropIndex('frames_kode_index');
        });

        Schema::table('aksesoris', function (Blueprint $table) {
            $table->dropIndex('aksesoris_branch_name_index');
            $table->dropIndex('aksesoris_name_index');
        });
    }
};
