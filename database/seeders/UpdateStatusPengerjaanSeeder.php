<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaksi;

class UpdateStatusPengerjaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaksi::whereNull('status_pengerjaan')
                 ->update(['status_pengerjaan' => 'Menunggu Pengerjaan']);

        $this->command->info('Status pengerjaan untuk transaksi lama telah diperbarui.');
    }
}
