<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplateSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_template_is_used_for_manual_message_preview_and_save(): void
    {
        $rendered = WhatsAppMessage::buildMessage([
            'nombre' => 'Lucía',
            'apellidos' => 'García',
            'telefono' => '611222333',
            'scheduled_for' => now()->setDate(2026, 6, 22)->setTime(9, 5),
        ], 'formal_reminder');

        $this->assertSame(
            'Estimado/a Lucía García, le recordamos su cita el 22/06/2026 a las 09:05. Saludos, Clínica Dental Eugénia',
            $rendered
        );
    }
}
