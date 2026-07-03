<?php

namespace Tests\Feature;

use App\Livewire\WhatsAppConnectionTest;
use App\Models\WhatsAppTemplate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppConnectionComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cloud_api_test_message_uses_template_payload_for_the_saved_recipient(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        Config::set('whatsapp.driver', 'cloud_api');
        Config::set('whatsapp.default_template', 'confirmar_cita');
        Config::set('whatsapp.meta.phone_number_id', '1234567890');
        Config::set('whatsapp.meta.access_token', 'test-token');
        Config::set('whatsapp.meta.base_url', 'https://graph.facebook.com');
        Config::set('whatsapp.meta.version', 'v22.0');
        Config::set('whatsapp.meta.test_recipient', '+34618287914');
        WhatsAppTemplate::flushCatalogCache();

        $sentPayload = null;

        Http::fake(function ($request) use (&$sentPayload) {
            $sentPayload = $request->data();

            return Http::response([
                'messages' => [
                    ['id' => 'wamid.TEST123'],
                ],
            ], 200);
        });

        Livewire::test(WhatsAppConnectionTest::class)
            ->call('sendSavedRecipient')
            ->assertSet('status', 'Prueba enviada correctamente.')
            ->assertSet('statusType', 'success')
            ->assertSet('details.provider', 'cloud_api')
            ->assertSet('details.to', '+34618287914');

        $this->assertIsArray($sentPayload);
        $this->assertSame('template', $sentPayload['type']);
        $this->assertSame('confirmar_cita', $sentPayload['template']['name']);
        $this->assertSame('+34618287914', $sentPayload['to']);

        Carbon::setTestNow();
    }
}
