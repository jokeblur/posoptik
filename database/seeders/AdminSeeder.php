<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Super Admin user
        User::firstOrCreate(
            ['email' => 'superadmin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('12345678'),
                'role' => 'super admin',
                'branch_id' => 1, // Default branch
            ]
        );
    }
}
