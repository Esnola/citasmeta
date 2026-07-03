<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_admin_user_creation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk();

        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }
}
