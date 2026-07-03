<?php

namespace Tests\Feature;

use App\Livewire\WhatsAppTemplateManager;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppTemplateManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_template_and_mark_it_as_default(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(WhatsAppTemplateManager::class)
            ->set('key', 'postop-reminder')
            ->set('label', 'Recordatorio postoperatorio')
            ->set('message', 'Hola [NOMBRE], revisa el [DIA] a las [HORA].')
            ->set('is_default', true)
            ->set('sort_order', 10)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_templates', [
            'key' => 'postop-reminder',
            'label' => 'Recordatorio postoperatorio',
            'is_default' => true,
        ]);

        $rendered = WhatsAppMessage::buildMessage([
            'nombre' => 'Laura',
            'apellidos' => 'López',
            'telefono' => '600111222',
            'scheduled_for' => now()->setDate(2026, 6, 22)->setTime(8, 30),
        ], 'postop-reminder');

        $this->assertSame('Hola Laura, revisa el 22/06/2026 a las 08:30.', $rendered);

        $this->assertSame('postop-reminder', WhatsAppTemplate::defaultKey());
    }

    public function test_admin_can_edit_a_template(): void
    {
        $admin = User::factory()->create();
        $template = WhatsAppTemplate::query()->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(WhatsAppTemplateManager::class)
            ->call('edit', $template->id)
            ->assertSeeHtml('type="submit"')
            ->set('label', 'Recordatorio actualizado')
            ->set('message', 'Hola [NOMBRE], tu cita cambia al [DIA].')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_templates', [
            'id' => $template->id,
            'label' => 'Recordatorio actualizado',
            'message' => 'Hola [NOMBRE], tu cita cambia al [DIA].',
        ]);
    }
}
