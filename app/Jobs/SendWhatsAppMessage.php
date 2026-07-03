<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use App\Services\WhatsApp\WhatsAppSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendWhatsAppMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [5, 15, 30];

    public function __construct(
        public int $messageId,
    ) {}

    public function handle(WhatsAppSender $sender, AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): void
    {
        $message = WhatsAppMessage::query()->findOrFail($this->messageId);

        $result = $sender->send($message);

        $providerStatus = (string) (data_get($result, 'raw.status') ?? data_get($result, 'raw.messages.0.status', ''));

        $message->update([
            'status' => $this->resolveStatus($providerStatus),
            'sent_at' => $this->isAcceptedStatus($providerStatus) ? now() : null,
            'last_error' => null,
            'provider_message_id' => $result['message_id'],
            'provider_payload' => [
                'provider' => $result['provider'],
                'payload' => $result['payload'],
                'raw' => $result['raw'],
            ],
        ]);

        if ($this->isAcceptedStatus($providerStatus) && $message->appointment) {
            $message->appointment->update([
                'enviado' => true,
                'whatsapp_sent_at' => now(),
            ]);

            $deliveryStatusSyncer->sync([$message->appointment_id]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $message = WhatsAppMessage::query()->find($this->messageId);

        if ($message) {
            $message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'last_error' => $exception?->getMessage() ?? 'Job failed after maximum retries.',
            ]);
        }
    }

    private function resolveStatus(string $providerStatus): string
    {
        if (in_array($providerStatus, ['sent', 'delivered', 'accepted', 'queued', 'sending'], true)) {
            return WhatsAppMessage::STATUS_SENT;
        }

        if (in_array($providerStatus, ['failed', 'undelivered'], true)) {
            return WhatsAppMessage::STATUS_FAILED;
        }

        return WhatsAppMessage::STATUS_PENDING;
    }

    private function isAcceptedStatus(string $providerStatus): bool
    {
        return in_array($providerStatus, ['sent', 'delivered', 'accepted', 'queued', 'sending'], true);
    }
}
