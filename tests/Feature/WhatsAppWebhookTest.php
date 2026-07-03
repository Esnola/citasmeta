<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_webhook_verification_returns_the_challenge(): void
    {
        Config::set('whatsapp.meta.verify_token', 'verify-me');

        $this->get('/webhooks/whatsapp/meta?hub.mode=subscribe&hub.verify_token=verify-me&hub.challenge=123456')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('123456', false);
    }

    public function test_meta_webhook_updates_delivery_state_from_signed_callback(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        $appointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-30',
            'hora' => '11:45',
            'enviado' => true,
            'entregado' => false,
            'activo' => true,
        ]);

        WhatsAppMessage::query()->create([
            'client_id' => $client->id,
            'appointment_id' => $appointment->id,
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
            'scheduled_for' => now()->subMinute(),
            'message' => 'Hola Ana',
            'source' => WhatsAppMessage::SOURCE_APPOINTMENT,
            'status' => WhatsAppMessage::STATUS_SENT,
            'provider_message_id' => 'wamid.TEST123',
        ]);

        Config::set('whatsapp.meta.app_secret', 'app-secret');
        Config::set('whatsapp.meta.waba_id', 'WABAID');

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABAID',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'statuses' => [
                                    [
                                        'id' => 'wamid.TEST123',
                                        'status' => 'delivered',
                                        'timestamp' => '1719144000',
                                        'recipient_id' => '34600123123',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->withHeader('X-Hub-Signature-256', $this->signatureFor($payload))
            ->postJson('/webhooks/whatsapp/meta', $payload)
            ->assertNoContent();

        $appointment->refresh();
        $message = WhatsAppMessage::query()->firstOrFail();

        $this->assertTrue($appointment->entregado);
        $this->assertNotNull($appointment->whatsapp_delivered_at);
        $this->assertSame('delivered', $message->deliveryStatus());
        $this->assertSame('delivered', $message->provider_payload['callback']['message_status']);

        Carbon::setTestNow();
    }

    public function test_meta_webhook_rejects_invalid_signatures(): void
    {
        Config::set('whatsapp.meta.app_secret', 'app-secret');

        $this->withHeader('X-Hub-Signature-256', 'sha256=invalid')
            ->postJson('/webhooks/whatsapp/meta', [
                'entry' => [],
            ])
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function signatureFor(array $payload): string
    {
        return 'sha256='.hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'app-secret');
    }
}
