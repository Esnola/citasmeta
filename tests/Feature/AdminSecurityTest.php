<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_security_screen(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.security.edit'))
            ->assertOk();

        $regular = User::factory()->create();

        $this->actingAs($regular)
            ->get(route('admin.security.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_change_own_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.security.update'), [
                'password' => 'NewSecret12345!',
                'password_confirmation' => 'NewSecret12345!',
            ])
            ->assertRedirect(route('admin.security.edit'));

        $admin->refresh();

        $this->assertTrue(Hash::check('NewSecret12345!', $admin->password));
    }
}
