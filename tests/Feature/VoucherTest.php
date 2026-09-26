<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_discount_voucher()
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->post('/voucher', [
            'kode' => 'DISC-TEST-01',
            'jenis_nominal' => 'diskon',
            'nominal' => 20,
            'syarat_ketentuan' => 'Diskon khusus member',
            'berlaku_mulai' => now()->toDateString(),
            'berlaku_sampai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $response->assertRedirect('/voucher');
        $this->assertDatabaseHas('vouchers', [
            'kode' => 'DISC-TEST-01',
            'jenis_nominal' => 'diskon',
            'nominal' => 20.00,
        ]);
    }
}
