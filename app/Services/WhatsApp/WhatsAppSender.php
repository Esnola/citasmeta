<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
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
            'cloud_api' => ($mode === 'text')
                ? $this->sendTestViaCloudApiText($recipient, $body)
                : $this->sendTestViaCloudApiTemplate($recipient),
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
        $config = config('whatsapp.meta', config('whatsapp.cloud_api', []));
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (! $phoneNumberId || ! $accessToken) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $payload = $this->buildCloudApiPayload($message);

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
    private function sendTestViaCloudApiText(string $recipient, string $body): array
    {
        $config = config('whatsapp.meta', config('whatsapp.cloud_api', []));
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

    /**
     * @return array{provider:string,message_id:?string,payload:array,raw:array}
     *
     * @throws RequestException
     */
    private function sendTestViaCloudApiTemplate(string $recipient): array
    {
        $config = config('whatsapp.meta', config('whatsapp.cloud_api', []));
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (! $phoneNumberId || ! $accessToken) {
            throw new RuntimeException('WhatsApp Cloud API credentials are not configured.');
        }

        $normalizedRecipient = $this->normalizeInternationalPhone($recipient);
        $template = WhatsAppTemplate::resolve(config('whatsapp.default_template'));
        $scheduledFor = now();

        $payload = $this->buildTemplatePayloadFromValues(
            $normalizedRecipient,
            $template['key'],
            $template['message'],
            [
                '[NOMBRE]' => 'Prueba',
                '[APELLIDOS]' => '',
                '[TELEFONO]' => $normalizedRecipient,
                '[DIA]' => $scheduledFor->format('d/m/Y'),
                '[HORA]' => $scheduledFor->format('H:i'),
            ]
        );

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
        $recipient = config('whatsapp.meta.test_recipient')
            ?: config('whatsapp.cloud_api.test_recipient');

        return filled($recipient) ? (string) $recipient : null;
    }

    private function buildCloudApiPayload(WhatsAppMessage $message): array
    {
        return $this->buildTemplatePayload($message);
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

    private function buildTemplatePayload(WhatsAppMessage $message): array
    {
        $templateKey = (string) data_get($message->metadata, 'template_key', WhatsAppTemplate::defaultKey());
        $template = WhatsAppTemplate::resolve($templateKey);

        return $this->buildTemplatePayloadFromValues(
            $message->normalizedPhone(),
            $template['key'],
            $template['message'],
            [
                '[NOMBRE]' => $message->nombre,
                '[APELLIDOS]' => $message->apellidos,
                '[TELEFONO]' => $message->telefono,
                '[DIA]' => $message->scheduled_for?->format('d/m/Y'),
                '[HORA]' => $message->scheduled_for?->format('H:i'),
            ]
        );
    }

    /**
     * @param  array<string, string|null>  $replacements
     */
    private function buildTemplatePayloadFromValues(string $recipient, string $templateKey, string $templateMessage, array $replacements): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $recipient,
            'type' => 'template',
            'template' => [
                'name' => $templateKey,
                'language' => [
                    'code' => (string) config('whatsapp.template_language_code', 'es_ES'),
                ],
                'components' => $this->buildTemplateComponentsFromValues($templateMessage, $replacements),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTemplateComponentsFromValues(string $template, array $replacements): array
    {
        preg_match_all('/\[[A-Z_]+\]/', $template, $matches);

        $placeholders = array_values(array_unique($matches[0] ?? []));

        if ($placeholders === []) {
            return [];
        }

        $parameters = array_values(array_filter(array_map(
            static fn (string $placeholder): ?array => isset($replacements[$placeholder])
                ? [
                    'type' => 'text',
                    'text' => (string) $replacements[$placeholder],
                ]
                : null,
            $placeholders
        )));

        return $parameters === [] ? [] : [[
            'type' => 'body',
            'parameters' => $parameters,
        ]];
    }
}
