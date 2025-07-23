<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role; // This model might not be used anymore but leaving it for now

class PassetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Passet',
            'email' => 'passet@admin.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_PASSET_BANTU, // Using the constant from the User model
            // You might need to assign a branch_id here as well
            // 'branch_id' => 1,
        ]);
    }
}
