<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FrameExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_export_frame_data_as_excel()
    {
        // Buat user super admin agar bisa akses fitur export
        $user = \App\Models\User::factory()->create([
            'role' => \App\Models\User::ROLE_SUPER_ADMIN,
            'branch_id' => 1,
        ]);
        $this->actingAs($user instanceof \App\Models\User ? $user : $user->first());

        // Panggil route export
        $response = $this->get('/frame/export');

        // Pastikan response adalah file excel
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('content-disposition'));
    }
} 