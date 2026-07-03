<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentReminderPreference;
use App\Models\Client;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppDispatchCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_messages_are_sent_via_cloud_api_and_marked_as_sent(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $admin = User::factory()->create();

        WhatsAppMessage::query()->create([
            'user_id' => $admin->id,
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
            'scheduled_for' => now()->subMinute(),
            'message' => 'Hola Ana',
            'source' => WhatsAppMessage::SOURCE_MANUAL,
            'status' => WhatsAppMessage::STATUS_PENDING,
        ]);

        Config::set('whatsapp.driver', 'cloud_api');
        Config::set('whatsapp.meta.phone_number_id', '1234567890');
        Config::set('whatsapp.meta.access_token', 'test-token');
        Config::set('whatsapp.meta.base_url', 'https://graph.facebook.com');
        Config::set('whatsapp.meta.version', 'v22.0');
        Config::set('whatsapp.default_country_code', '+34');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'graph.facebook.com')) {
                return Http::response([
                    'messages' => [
                        ['id' => 'wamid.TEST123'],
                    ],
                ], 200);
            }

            return Http::response([], 200);
        });

        $this->artisan('whatsapp:dispatch-due')->assertExitCode(0);

        $message = WhatsAppMessage::query()->firstOrFail();

        $this->assertSame(WhatsAppMessage::STATUS_SENT, $message->status);
        $this->assertSame('wamid.TEST123', $message->provider_message_id);
        $this->assertSame('cloud_api', $message->provider_payload['provider']);
        $this->assertSame('template', $message->provider_payload['payload']['type']);
        $this->assertSame('clinical_reminder', $message->provider_payload['payload']['template']['name']);
        $this->assertSame('Ana', $message->provider_payload['payload']['template']['components'][0]['parameters'][0]['text']);
        $this->assertSame('23/06/2026', $message->provider_payload['payload']['template']['components'][0]['parameters'][1]['text']);
        $this->assertSame('11:59', $message->provider_payload['payload']['template']['components'][0]['parameters'][2]['text']);
        $this->assertNotNull($message->sent_at);

        Carbon::setTestNow();
    }

    public function test_active_unsent_due_appointments_are_queued_sent_and_marked_as_sent(): void
    {
        Carbon::setTestNow('2026-06-23 12:00:00');

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        $appointment = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-24',
            'hora' => '11:45',
            'enviado' => false,
            'activo' => true,
            'cita_activa' => true,
        ]);

        Config::set('whatsapp.driver', 'cloud_api');
        Config::set('whatsapp.meta.phone_number_id', '123456');
        Config::set('whatsapp.meta.access_token', 'test-token');
        Config::set('whatsapp.message_mode', 'template');
        Config::set('whatsapp.default_country_code', '+34');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'graph.facebook.com')) {
                return Http::response([
                    'messages' => [['id' => 'wamid.DISPATCHDUE123', 'status' => 'accepted']],
                ], 200);
            }

            return Http::response([], 200);
        });

        $this->artisan('whatsapp:dispatch-due')->assertExitCode(0);

        $message = WhatsAppMessage::query()->firstOrFail();

        $this->assertSame($appointment->id, $message->appointment_id);
        $this->assertSame($client->id, $message->client_id);
        $this->assertSame(WhatsAppMessage::SOURCE_APPOINTMENT, $message->source);
        $this->assertSame(WhatsAppMessage::STATUS_SENT, $message->status);
        $this->assertSame('wamid.DISPATCHDUE123', $message->provider_message_id);
        $this->assertSame(1, $message->metadata['lead_days']);
        $appointment->refresh();

        $this->assertTrue($appointment->enviado);
        $this->assertFalse($appointment->activo);
        $this->assertTrue($appointment->cita_activa);

        $this->artisan('whatsapp:dispatch-due')->assertExitCode(0);

        $this->assertSame(1, WhatsAppMessage::query()->count());

        Carbon::setTestNow();
    }

    public function test_active_appointments_are_queued_for_selected_whatsapp_lead_days(): void
    {
        Carbon::setTestNow('2026-06-22 12:00:00');

        AppointmentReminderPreference::saveSelections([
            AppointmentReminderPreference::CHANNEL_WHATSAPP => [1, 2, 7],
            AppointmentReminderPreference::CHANNEL_EMAIL => [3],
        ]);

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '600123123',
        ]);

        $appointment1 = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-23',
            'hora' => '11:45',
            'enviado' => false,
            'activo' => true,
            'cita_activa' => true,
        ]);

        $appointment2 = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-24',
            'hora' => '11:45',
            'enviado' => false,
            'activo' => true,
            'cita_activa' => true,
        ]);

        $appointment3 = Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => '2026-06-29',
            'hora' => '11:45',
            'enviado' => false,
            'activo' => true,
            'cita_activa' => true,
        ]);

        Config::set('whatsapp.driver', 'log');

        $this->artisan('whatsapp:dispatch-due')->assertExitCode(0);

        $this->assertSame(3, WhatsAppMessage::query()->count());

        Carbon::setTestNow('2026-06-23 12:00:00');

        $this->artisan('whatsapp:dispatch-due')->assertExitCode(0);

        $this->assertSame(3, WhatsAppMessage::query()->count());
        $this->assertSame(3, WhatsAppMessage::query()->where('status', WhatsAppMessage::STATUS_SENT)->count());

        Carbon::setTestNow();
    }

    public function test_delivery_sync_command_marks_appointments_as_delivered_when_logs_show_delivered(): void
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
            'provider_message_id' => 'SMLOG123',
            'provider_payload' => [
                'provider' => 'cloud_api',
                'raw' => [
                    'status' => 'delivered',
                ],
            ],
        ]);

        $this->artisan('whatsapp:sync-delivery-status')->assertExitCode(0);

        $this->assertTrue($appointment->refresh()->entregado);
        $this->assertNotNull($appointment->whatsapp_delivered_at);

        Carbon::setTestNow();
    }

    public function test_backfill_command_populates_appointment_delivery_timestamps_from_stored_messages(): void
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
            'enviado' => false,
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
            'sent_at' => '2026-06-23 08:05:00',
            'provider_message_id' => 'SMBACKFILL123',
            'provider_payload' => [
                'provider' => 'cloud_api',
                'raw' => [
                    'status' => 'delivered',
                ],
                'callback' => [
                    'message_status' => 'read',
                    'event_type' => 'READ',
                    'received_at' => '2026-06-23 08:12:00',
                    'payload' => [],
                ],
            ],
        ]);

        $this->artisan('whatsapp:backfill-appointment-delivery-state')->assertExitCode(0);

        $appointment->refresh();

        $this->assertTrue($appointment->enviado);
        $this->assertTrue($appointment->entregado);
        $this->assertSame('2026-06-23 08:05:00', $appointment->whatsapp_sent_at?->toDateTimeString());
        $this->assertSame('2026-06-23 08:12:00', $appointment->whatsapp_delivered_at?->toDateTimeString());
        $this->assertSame('2026-06-23 08:12:00', $appointment->whatsapp_read_at?->toDateTimeString());

        Carbon::setTestNow();
    }
}
