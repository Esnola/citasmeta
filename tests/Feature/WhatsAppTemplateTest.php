<?php

namespace Tests\Feature;

use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_template_replaces_placeholders_from_data(): void
    {
        $message = WhatsAppMessage::buildMessage([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
            'scheduled_for' => now()->setDate(2026, 6, 22)->setTime(15, 30),
        ], 'Hola [NOMBRE] [APELLIDOS], tu cita es el [DIA] a las [HORA]. Tel: [TELEFONO]');

        $this->assertSame('Hola Ana Pérez, tu cita es el 22/06/2026 a las 15:30. Tel: 600123123', $message);
    }
}
