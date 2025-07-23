<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari cabang berdasarkan nama, atau buat jika tidak ada
        $branch = Branch::firstOrCreate(
            ['name' => 'Optik Melati Cabang 1'],
            [
                'code' => 'CAB001',
                'address' => 'Alamat Cabang 1 Default',
                'phone' => '000000000',
                'is_active' => true,
            ]
        );

        // Buat user kasir dan hubungkan ke cabang
        User::firstOrCreate(
            ['email' => 'dd@gmail.com'],
            [
                'name' => 'dicky',
                'password' => Hash::make('12345678'),
                'role' => 'kasir',
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
