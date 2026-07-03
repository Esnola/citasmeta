<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class AppointmentImmediateSender
{
    /**
     * @return array{sent: bool, message: string}
     */
    public function send(
        Appointment $appointment,
        Client $client,
        WhatsAppSender $sender,
        string $successMessage,
        string $failureMessage,
    ): array {
        $scheduledFor = $appointment->scheduledFor();
        $message = WhatsAppMessage::query()->create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'appointment_id' => $appointment->id,
            'nombre' => $client->nombre,
            'apellidos' => $client->apellidos,
            'telefono' => $client->telefono,
            'scheduled_for' => $scheduledFor,
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
                'immediate_send' => true,
                'immediate_sent_at' => now()->toDateTimeString(),
            ],
        ]);

        try {
            SendWhatsAppMessage::dispatchSync($message->id);

            $message->refresh();

            if ($message->status === WhatsAppMessage::STATUS_SENT) {
                $appointment->refresh();

                return [
                    'sent' => true,
                    'message' => $successMessage,
                ];
            }

            $errorDetail = $message->last_error ?? '';

            return [
                'sent' => false,
                'message' => $failureMessage.($errorDetail !== '' ? ' '.$errorDetail.'.' : '').' La cita no se ha marcado como enviada.',
            ];
        } catch (Throwable $throwable) {
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'last_error' => $throwable->getMessage(),
            ]);

            return [
                'sent' => false,
                'message' => $failureMessage.' Error: '.Str::limit($throwable->getMessage(), 220).'. La cita no se ha marcado como enviada.',
            ];
        }
    }
}
