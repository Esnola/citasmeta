<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_users_as_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create([
            'name' => 'Laura',
            'email' => 'laura@example.com',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.export.users'))
            ->assertOk()
            ->assertDownload('usuarios.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Nombre,Correo,Administrador', $content);
        $this->assertStringContainsString('Laura,laura@example.com,No', $content);
        $this->assertStringContainsString($admin->name, $content);
    }
}
