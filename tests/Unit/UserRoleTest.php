<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_can_access_frame_menu_for_normalized_roles(): void
    {
        $kasirUser = new User(['role' => ' Kasir ']);
        $this->assertTrue($kasirUser->canAccessFrameMenu());

        $superAdminUser = new User(['role' => 'SUPER ADMIN']);
        $this->assertTrue($superAdminUser->canAccessFrameMenu());

        $passetUser = new User(['role' => 'passet bantu']);
        $this->assertFalse($passetUser->canAccessFrameMenu());
    }
}
