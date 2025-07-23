<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('branches')->insert([
            [
                'name' => 'Optik Melati Cabang 1',
                'code' => 'CAB001',
                'address' => 'Jl. Raya Utama No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'cabang1@optikmelati.com',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Optik Melati Cabang 2',
                'code' => 'CAB002',
                'address' => 'Jl. Sudirman No. 456, Jakarta Selatan',
                'phone' => '021-87654321',
                'email' => 'cabang2@optikmelati.com',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
