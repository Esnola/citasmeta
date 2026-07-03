<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentReminderPreference;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use App\Services\WhatsApp\WhatsAppSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchDueWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:dispatch-due';

    protected $description = 'Dispatch all due WhatsApp messages.';

    public function handle(WhatsAppSender $sender, AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): int
    {
        try {
            $queued = $this->queueActiveAppointmentMessages();
            $count = 0;

            WhatsAppMessage::due()
                ->with('appointment')
                ->chunkById(100, function ($messages) use (&$count, $sender, $deliveryStatusSyncer): void {
                    foreach ($messages as $message) {
                        if ($message->appointment && ! $message->appointment->activo) {
                            continue;
                        }

                        try {
                            $result = $sender->send($message);

                            $providerStatus = (string) (data_get($result, 'raw.status') ?? data_get($result, 'raw.messages.0.status', ''));
                            $isFailed = in_array($providerStatus, ['failed', 'undelivered'], true);

                            $message->update([
                                'status' => $isFailed ? WhatsAppMessage::STATUS_FAILED : WhatsAppMessage::STATUS_SENT,
                                'sent_at' => $isFailed ? null : now(),
                                'last_error' => null,
                                'provider_message_id' => $result['message_id'],
                                'provider_payload' => [
                                    'provider' => $result['provider'],
                                    'payload' => $result['payload'],
                                    'raw' => $result['raw'],
                                ],
                            ]);

                            if (! $isFailed && $message->appointment) {
                                $message->appointment->update([
                                    'enviado' => true,
                                    'whatsapp_sent_at' => now(),
                                ]);
                            }

                            $deliveryStatusSyncer->sync([$message->appointment_id]);
                        } catch (Throwable $throwable) {
                            $message->update([
                                'status' => WhatsAppMessage::STATUS_FAILED,
                                'last_error' => $throwable->getMessage(),
                            ]);

                            Log::channel('whatsapp_error')->error('WhatsApp send failed', [
                                'message_id' => $message->id,
                                'appointment_id' => $message->appointment_id,
                                'client_id' => $message->client_id,
                                'telefono' => $message->telefono,
                                'error' => $throwable->getMessage(),
                            ]);

                            $this->error("Failed to send message {$message->id}: {$throwable->getMessage()}");
                        }

                        $count++;
                    }
                });

            $this->info(sprintf('Queued %d appointment message(s).', $queued));
            $this->info(sprintf('Processed %d due message(s).', $count));

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            Log::channel('whatsapp_error')->error('WhatsApp dispatch command failed', [
                'error' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }

    private function queueActiveAppointmentMessages(): int
    {
        $queued = 0;

        foreach (AppointmentReminderPreference::enabledLeadDaysFor(AppointmentReminderPreference::CHANNEL_WHATSAPP) as $leadDays) {
            $targetDate = now(config('app.timezone'))->addDays($leadDays)->toDateString();

            Appointment::query()
                ->with('client')
              // ->where('client_id', 1)
                ->where('activo', true)
                ->where('cita_activa', true)
                ->whereDate('fecha', $targetDate)
                ->chunkById(100, function ($appointments) use (&$queued, $leadDays): void {
                    foreach ($appointments as $appointment) {
                        $client = $appointment->client;

                        if (! $client || ! $client->telefono) {
                            continue;
                        }

                        if ($this->appointmentReminderExists($appointment, $leadDays)) {
                            continue;
                        }

                        $scheduledFor = $appointment->scheduledFor();

                        WhatsAppMessage::query()->create([
                            'client_id' => $client->id,
                            'appointment_id' => $appointment->id,
                            'nombre' => $client->nombre,
                            'apellidos' => $client->apellidos,
                            'telefono' => $client->telefono,
                            'scheduled_for' => now(),
                            'message' => WhatsAppMessage::buildMessage([
                                'nombre' => $client->nombre,
                                'apellidos' => $client->apellidos,
                                'telefono' => $client->telefono,
                                'scheduled_for' => $scheduledFor,
                            ]),
                            'source' => WhatsAppMessage::SOURCE_APPOINTMENT,
                            'status' => WhatsAppMessage::STATUS_PENDING,
                            'metadata' => [
                                'origin_appointment_id' => $appointment->id,
                                'channel' => AppointmentReminderPreference::CHANNEL_WHATSAPP,
                                'lead_days' => $leadDays,
                            ],
                        ]);

                        $queued++;
                    }
                });
        }

        return $queued;
    }

    private function appointmentReminderExists(Appointment $appointment, int $leadDays): bool
    {
        return $appointment->whatsAppMessages()
            ->get()
            ->contains(function (WhatsAppMessage $message) use ($leadDays): bool {
                $metadata = $message->metadata ?? [];

                return ($metadata['channel'] ?? null) === AppointmentReminderPreference::CHANNEL_WHATSAPP
                  && (int) ($metadata['lead_days'] ?? 0) === $leadDays;
            });
    }
}
