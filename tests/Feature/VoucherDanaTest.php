<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherDanaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function voucherUang(string $kode, int $nominal): array
    {
        return [
            'kode' => $kode,
            'jenis_nominal' => 'uang',
            'nominal' => $nominal,
            'aktif' => true,
        ];
    }

    public function test_voucher_uang_ditolak_jika_dana_tidak_cukup()
    {
        $response = $this->actingAs($this->admin())->post('/voucher', $this->voucherUang('UANG-01', 50000));

        $response->assertSessionHasErrors('nominal');
        $this->assertDatabaseMissing('vouchers', ['kode' => 'UANG-01']);
    }

    public function test_saldo_voucher_diambil_dari_dana_dan_kembali_saat_dihapus()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/voucher/dana', ['jenis' => 'isi', 'jumlah' => 100000])
            ->assertRedirect('/voucher');
        $this->actingAs($admin)->post('/voucher', $this->voucherUang('UANG-02', 60000))
            ->assertRedirect('/voucher');
        $this->assertEquals(40000, VoucherDana::utama()->saldo);

        $voucher = Voucher::where('kode', 'UANG-02')->firstOrFail();
        $this->actingAs($admin)->post('/voucher/' . $voucher->id . '/saldo', ['jumlah' => 50000])
            ->assertSessionHasErrors('jumlah');
        $this->actingAs($admin)->post('/voucher/' . $voucher->id . '/saldo', ['jumlah' => 40000]);
        $this->assertEquals(0, VoucherDana::utama()->saldo);
        $this->assertEquals(100000, $voucher->fresh()->saldo);

        $this->actingAs($admin)->delete('/voucher/' . $voucher->id);
        $this->assertEquals(100000, VoucherDana::utama()->saldo);
    }

    public function test_tarik_dana_tidak_boleh_melebihi_saldo()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/voucher/dana', ['jenis' => 'isi', 'jumlah' => 10000]);

        $this->actingAs($admin)->post('/voucher/dana', ['jenis' => 'tarik', 'jumlah' => 20000])
            ->assertSessionHasErrors('jumlah');
        $this->assertEquals(10000, VoucherDana::utama()->saldo);
    }
}
