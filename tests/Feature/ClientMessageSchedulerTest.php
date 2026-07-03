<?php

namespace Tests\Feature;

use App\Livewire\ClientMessageScheduler;
use App\Models\Client;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ClientMessageSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_creates_message_linked_to_client(): void
    {
        Carbon::setTestNow('2026-06-22 15:30:00');

        $admin = User::factory()->create();
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        $this->actingAs($admin);

        Livewire::test(ClientMessageScheduler::class)
            ->call('selectClient', $client->id)
            ->set('scheduled_date', '2026-06-24')
            ->set('scheduled_time', '11:20')
            ->call('save')
            ->assertSee('Mensaje programado desde la ficha del cliente.');

        $message = WhatsAppMessage::query()->firstOrFail();

        $this->assertSame($client->id, $message->client_id);
        $this->assertSame('Ana', $message->nombre);
        $this->assertSame('Pérez', $message->apellidos);
        $this->assertSame('600123123', $message->telefono);
        $this->assertSame('2026-06-24 11:20:00', $message->scheduled_for->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_scheduler_rejects_today_and_sundays(): void
    {
        Carbon::setTestNow('2026-06-23 15:30:00');

        $admin = User::factory()->create();
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        $this->actingAs($admin);

        Livewire::test(ClientMessageScheduler::class)
            ->call('selectClient', $client->id)
            ->set('scheduled_date', '2026-06-23')
            ->set('scheduled_time', '11:20')
            ->call('save')
            ->assertHasErrors('scheduled_date')
            ->assertSee('La fecha debe ser posterior a hoy.');

        Livewire::test(ClientMessageScheduler::class)
            ->call('selectClient', $client->id)
            ->set('scheduled_date', '2026-06-28')
            ->set('scheduled_time', '11:20')
            ->call('save')
            ->assertHasErrors('scheduled_date');

        $this->assertSame(0, WhatsAppMessage::query()->count());

        Carbon::setTestNow();
    }

    public function test_scheduler_default_date_skips_sunday(): void
    {
        Carbon::setTestNow('2026-06-27 15:30:00');

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        Livewire::test(ClientMessageScheduler::class)
            ->call('selectClient', $client->id)
            ->assertSet('scheduled_date', '2026-06-29');

        Carbon::setTestNow();
    }

    public function test_scheduler_can_send_selected_client_message_immediately(): void
    {
        Carbon::setTestNow('2026-06-23 15:30:00');

        $admin = User::factory()->create();
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        Config::set('whatsapp.driver', 'cloud_api');
        Config::set('whatsapp.cloud_api.phone_number_id', '123456');
        Config::set('whatsapp.cloud_api.access_token', 'test-token');
        Config::set('whatsapp.message_mode', 'template');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'graph.facebook.com')) {
                return Http::response([
                    'messages' => [['id' => 'wamid.IMMEDIATE123', 'status' => 'accepted']],
                ], 200);
            }

            return Http::response([], 200);
        });

        $this->actingAs($admin);

        Livewire::test(ClientMessageScheduler::class)
            ->call('selectClient', $client->id)
            ->set('scheduled_date', '2026-06-24')
            ->set('scheduled_time', '10:15')
            ->call('sendNow')
            ->assertSee('WhatsApp enviado ahora y registrado correctamente.');

        $message = WhatsAppMessage::query()->firstOrFail();

        $this->assertSame(WhatsAppMessage::STATUS_SENT, $message->status);
        $this->assertSame($client->id, $message->client_id);
        $this->assertSame('2026-06-24 10:15:00', $message->scheduled_for->toDateTimeString());
        $this->assertSame('wamid.IMMEDIATE123', $message->provider_message_id);
        $this->assertTrue($message->metadata['immediate_send']);
        $this->assertSame('2026-06-23 15:30:00', $message->metadata['immediate_sent_at']);
        $this->assertNotNull($message->sent_at);

        Carbon::setTestNow();
    }

    public function test_scheduler_can_preselect_client_from_query_string(): void
    {
        Carbon::setTestNow('2026-06-22 15:30:00');

        $admin = User::factory()->create();
        $client = Client::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Martín',
            'telefono' => '+34666777888',
        ]);

        $this->actingAs($admin)
            ->get(route('clients.index', ['client' => $client->id]))
            ->assertOk()
            ->assertSee('Programar desde cliente')
            ->assertSee('Lucía Martín')
            ->assertSee('666777888');

        Carbon::setTestNow();
    }
}
