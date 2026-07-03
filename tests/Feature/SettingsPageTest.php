<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_loads_with_collapsible_sections(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Ajustes')
            ->assertSee('Cloud API (Meta)')
            ->assertSee('Prueba de conexión')
            ->assertSee('Plantillas')
            ->assertDontSee('data-settings-section="overview" draggable', false)
            ->assertDontSee('data-settings-section="connection" draggable', false);
    }
}
