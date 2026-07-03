<?php

namespace App\Services\WhatsApp;

use App\Models\Appointment;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AppointmentDeliveryStatusSyncer
{
    public function syncAll(?int $clientId = null, bool $force = false): int
    {
        if (! $this->canSync()) {
            return 0;
        }

        $messages = WhatsAppMessage::query()
            ->whereNotNull('appointment_id')
            ->when($clientId, fn ($query) => $query->where('client_id', $clientId))
            ->get(['id', 'appointment_id', 'provider_message_id', 'sent_at', 'created_at', 'provider_payload']);

        return $this->syncAppointmentsFromMessages($messages);
    }

    public function backfillFromStoredMessages(?int $clientId = null): int
    {
        if (! $this->canSync()) {
            return 0;
        }

        $messages = WhatsAppMessage::query()
            ->whereNotNull('appointment_id')
            ->when($clientId, fn ($query) => $query->where('client_id', $clientId))
            ->get(['id', 'appointment_id', 'sent_at', 'created_at', 'provider_payload']);

        return $this->syncAppointmentsFromMessages($messages);
    }

    /**
     * @param  iterable<int>|Collection<int, int>  $appointmentIds
     */
    public function sync(iterable $appointmentIds, bool $force = false): int
    {
        if (! $this->canSync()) {
            return 0;
        }

        $ids = collect($appointmentIds)
            ->filter(fn (mixed $appointmentId): bool => (int) $appointmentId > 0)
            ->map(fn (mixed $appointmentId): int => (int) $appointmentId)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $messages = WhatsAppMessage::query()
            ->whereIn('appointment_id', $ids)
            ->whereNotNull('appointment_id')
            ->get(['id', 'appointment_id', 'provider_message_id', 'sent_at', 'created_at', 'provider_payload']);

        return $this->syncAppointmentsFromMessages($messages);
    }

    /**
     * Persist a delivery status update from a provider webhook and sync the related appointment state.
     *
     * @param  array<string, mixed>  $payload
     * @param  string  $provider  Provider name (e.g. 'cloud_api')
     * @param  string  $messageId  Provider message ID
     * @param  string  $status  New status (e.g. 'delivered', 'read')
     */
    public function syncFromProviderWebhook(string $provider, string $messageId, string $status, array $rawPayload = []): int
    {
        if (! $this->canSync()) {
            return 0;
        }

        if ($messageId === '') {
            return 0;
        }

        $message = WhatsAppMessage::query()
            ->where('provider_message_id', $messageId)
            ->first();

        if (! $message || ! $message->appointment_id) {
            return 0;
        }

        $providerPayload = $message->provider_payload ?? [];
        $providerPayload['callback'] = [
            'message_status' => strtolower(trim($status)),
            'received_at' => now()->toDateTimeString(),
            'payload' => $rawPayload,
        ];

        $message->update([
            'provider_payload' => $providerPayload,
        ]);

        return $this->sync([$message->appointment_id]);
    }

    private function canSync(): bool
    {
        return Schema::hasColumn('appointments', 'entregado');
    }

    /**
     * @param  Collection<int, WhatsAppMessage>  $messages
     */
    private function syncAppointmentsFromMessages(Collection $messages): int
    {
        $groupedMessages = $messages->groupBy('appointment_id');

        if ($groupedMessages->isEmpty()) {
            return 0;
        }

        $appointmentIds = $groupedMessages->keys()->all();
        $appointments = Appointment::query()->whereIn('id', $appointmentIds)->get()->keyBy('id');

        $updated = 0;

        foreach ($groupedMessages as $appointmentId => $appointmentMessages) {
            $appointment = $appointments->get($appointmentId);

            if (! $appointment) {
                continue;
            }

            $sentAt = $this->latestTimestamp($appointmentMessages->map(fn (WhatsAppMessage $message): ?Carbon => $message->sent_at));
            $deliveredAt = $this->latestTimestamp($appointmentMessages->map(fn (WhatsAppMessage $message): ?Carbon => $message->deliveredAt()));
            $readAt = $this->latestTimestamp($appointmentMessages->map(fn (WhatsAppMessage $message): ?Carbon => $message->readAt()));

            $newEnviado = $appointment->enviado || $sentAt !== null;
            $newActivo = $newEnviado ? false : $appointment->activo;
            $newSentAt = $this->latestTimestamp(collect([$appointment->whatsapp_sent_at, $sentAt]));
            $newEntregado = $appointment->entregado || $deliveredAt !== null;
            $newDeliveredAt = $this->latestTimestamp(collect([$appointment->whatsapp_delivered_at, $deliveredAt]));
            $newReadAt = $this->latestTimestamp(collect([$appointment->whatsapp_read_at, $readAt]));

            $dirty = $newEnviado !== $appointment->enviado
                || $newActivo !== $appointment->activo
                || $this->timestampDiffers($appointment->whatsapp_sent_at, $newSentAt)
                || $newEntregado !== $appointment->entregado
                || $this->timestampDiffers($appointment->whatsapp_delivered_at, $newDeliveredAt)
                || $this->timestampDiffers($appointment->whatsapp_read_at, $newReadAt);

            if ($dirty) {
                $appointment->update([
                    'enviado' => $newEnviado,
                    'activo' => $newActivo,
                    'whatsapp_sent_at' => $newSentAt,
                    'entregado' => $newEntregado,
                    'whatsapp_delivered_at' => $newDeliveredAt,
                    'whatsapp_read_at' => $newReadAt,
                ]);

                $updated++;
            }
        }

        return $updated;
    }

    private function timestampDiffers(?Carbon $current, ?Carbon $new): bool
    {
        if ($current === null && $new === null) {
            return false;
        }

        if ($current === null || $new === null) {
            return true;
        }

        return $current->ne($new);
    }

    /**
     * @param  Collection<int, Carbon|null>  $timestamps
     */
    private function latestTimestamp(Collection $timestamps): ?Carbon
    {
        return $timestamps
            ->filter(fn (?Carbon $timestamp): bool => $timestamp instanceof Carbon)
            ->sortBy(fn (Carbon $timestamp): int => $timestamp->getTimestamp())
            ->last();
    }
}
