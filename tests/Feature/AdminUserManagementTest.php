<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'name' => 'Before',
            'email' => 'before@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'After',
                'email' => 'after@example.com',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.create'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'After',
            'email' => 'after@example.com',
        ]);
    }

    public function test_edit_form_submits_a_boolean_admin_value(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('name="is_admin"', false)
            ->assertSee('value="1"', false)
            ->assertDontSee('value="is_admin"', false);
    }

    public function test_admin_cannot_remove_its_own_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
            ])
            ->assertStatus(422);

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_another_admin_can_remove_the_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'email' => $otherAdmin->email,
            ])
            ->assertRedirect(route('admin.users.create'));

        $this->assertFalse($otherAdmin->fresh()->is_admin);
    }

    public function test_user_deletion_uses_a_confirmation_modal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Eliminar usuario')
            ->assertSee($user->name)
            ->assertSeeHtml('aria-label="Cancelar"')
            ->assertSeeHtml('aria-label="Eliminar usuario"')
            ->assertSee('Esta acción no se puede deshacer.')
            ->assertDontSee('onsubmit="return confirm', false);
    }

    public function test_admin_can_delete_users_and_other_admins_but_not_itself(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherUser = User::factory()->create();
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherUser))
            ->assertRedirect(route('admin.users.create'));

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertRedirect(route('admin.users.create'));

        $this->assertModelMissing($otherAdmin);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertStatus(422);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Protegido');
    }
}
