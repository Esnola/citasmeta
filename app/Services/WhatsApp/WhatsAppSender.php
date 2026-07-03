<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessage;
use App\Traits\NormalizesPhone;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppSender
{
    use NormalizesPhone;

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     *
     * @throws RequestException
     */
    public function send(WhatsAppMessage $message): array
    {
        try {
            return match (config('whatsapp.driver')) {
                'cloud_api' => $this->sendViaCloudApi($message),
                'log' => $this->sendViaLog($message),
                default => throw new RuntimeException('Unsupported WhatsApp driver: '.config('whatsapp.driver')),
            };
        } catch (Throwable $throwable) {
            Log::channel('whatsapp_error')->error('WhatsApp send failed', [
                'message_id' => $message->id,
                'appointment_id' => $message->appointment_id,
                'client_id' => $message->client_id,
                'telefono' => $message->telefono,
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * Send a one-off test message without persisting a database record.
     *
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     */
    public function sendTestMessage(string $recipient, string $body, ?string $mode = null): array
    {
        return match (config('whatsapp.driver')) {
            'cloud_api' => $this->sendTestViaCloudApi($recipient, $body),
            'log' => $this->sendTestViaLog($recipient, $body),
            default => throw new RuntimeException('Unsupported WhatsApp driver: '.config('whatsapp.driver')),
        };
    }

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     */
    private function sendViaLog(WhatsAppMessage $message): array
    {
        $payload = $this->buildTextPayload($message);

        Log::info('WhatsApp message dispatched', [
            'provider' => 'log',
            'recipient' => $payload['to'],
            'name' => $message->full_name,
            'scheduled_for' => $message->scheduled_for?->toDateTimeString(),
            'message' => $message->message,
        ]);

        return [
            'provider' => 'log',
            'message_id' => null,
            'payload' => $payload,
            'raw' => [],
        ];
    }

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     */
    private function sendTestViaLog(string $recipient, string $body): array
    {
        $payload = [
            'to' => $recipient,
            'body' => $body,
        ];

        Log::info('WhatsApp test message dispatched', [
            'provider' => 'log',
            'recipient' => $recipient,
            'message' => $body,
        ]);

        return [
            'provider' => 'log',
            'message_id' => null,
            'payload' => $payload,
            'raw' => [],
        ];
    }

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     *
     * @throws RequestException
     */
    private function sendViaCloudApi(WhatsAppMessage $message): array
    {
        $config = config('whatsapp.cloud_api');
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (! $phoneNumberId || ! $accessToken) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $payload = $this->buildTextPayload($message);

        $response = Http::baseUrl(rtrim((string) ($config['base_url'] ?? 'https://graph.facebook.com'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken($accessToken)
            ->timeout((int) ($config['timeout'] ?? 15))
            ->connectTimeout(10)
            ->post(sprintf('/%s/%s/messages', $config['version'] ?? 'v22.0', $phoneNumberId), $payload)
            ->throw()
            ->json();

        return [
            'provider' => 'cloud_api',
            'message_id' => data_get($response, 'messages.0.id'),
            'payload' => $payload,
            'raw' => $response,
        ];
    }

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     *
     * @throws RequestException
     */
    private function sendTestViaCloudApi(string $recipient, string $body): array
    {
        $config = config('whatsapp.cloud_api');
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (! $phoneNumberId || ! $accessToken) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizeInternationalPhone($recipient),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ];

        $response = Http::baseUrl(rtrim((string) ($config['base_url'] ?? 'https://graph.facebook.com'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken($accessToken)
            ->timeout((int) ($config['timeout'] ?? 15))
            ->connectTimeout(10)
            ->post(sprintf('/%s/%s/messages', $config['version'] ?? 'v22.0', $phoneNumberId), $payload)
            ->throw()
            ->json();

        return [
            'provider' => 'cloud_api',
            'message_id' => data_get($response, 'messages.0.id'),
            'payload' => $payload,
            'raw' => $response,
        ];
    }

    public function testRecipient(): ?string
    {
        return null;
    }

    private function buildTextPayload(WhatsAppMessage $message): array
    {
        $body = $message->message;

        return [
            'messaging_product' => 'whatsapp',
            'to' => $message->normalizedPhone(),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ];
    }
}
