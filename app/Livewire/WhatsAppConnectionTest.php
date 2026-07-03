<?php

namespace App\Livewire;

use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppSender;
use App\Traits\NormalizesPhone;
use Illuminate\Support\Arr;
use Livewire\Component;
use Throwable;

class WhatsAppConnectionTest extends Component
{
    use NormalizesPhone;

    public string $recipient = '';

    public string $body = 'Mensaje de prueba desde Clínica Dental Eugenia.';

    public string $status = '';

    public string $statusType = 'neutral';

    public array $details = [];

    public function mount(): void
    {
        $this->recipient = '';
    }

    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:40'],
            'body' => ['required', 'string', 'max:500'],
        ];
    }

    public function sendSavedRecipient(WhatsAppSender $sender): void
    {
        $savedRecipient = $sender->testRecipient();

        if (! $savedRecipient) {
            $this->statusType = 'error';
            $this->status = 'Define META_WHATSAPP_TEST_RECIPIENT para usar este acceso rápido.';
            $this->details = [];

            return;
        }

        $this->recipient = $savedRecipient;
        $this->sendTest($sender);
    }

    public function sendTest(WhatsAppSender $sender): void
    {
        $data = $this->validate();

        try {
            $result = $sender->sendTestMessage($data['recipient'], $data['body']);

            $this->statusType = 'success';
            $this->status = 'Prueba enviada correctamente.';
            $this->details = [
                'provider' => $result['provider'],
                'message_id' => $result['message_id'],
                'to' => Arr::get($result, 'payload.to', $data['recipient']),
            ];
        } catch (Throwable $throwable) {
            $this->statusType = 'error';
            $this->status = $throwable->getMessage();
            $this->details = [];
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-connection-test', [
            'previewPayload' => $this->buildPreviewPayload(),
        ]);
    }

    private function buildPreviewPayload(): array
    {
        $recipient = $this->recipient !== '' ? $this->recipient : '';
        $preview = [
            'driver' => config('whatsapp.driver'),
            'recipient' => $recipient,
            'body' => $this->body,
        ];

        return match (config('whatsapp.driver')) {
            'cloud_api' => $this->buildCloudApiPreviewPayload($preview),
            default => $this->buildLogPreviewPayload($preview),
        };
    }

    private function buildCloudApiPreviewPayload(array $preview): array
    {
        $recipient = static::normalizeInternationalPhone($preview['recipient']);
        $template = WhatsAppTemplate::resolve(config('whatsapp.default_template'));
        $scheduledFor = now();

        return [
            'provider' => 'cloud_api',
            'request' => [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'template',
                'template' => [
                    'name' => $template['key'],
                    'language' => [
                        'code' => (string) config('whatsapp.template_language_code', 'es_ES'),
                    ],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'Prueba',
                            ],
                            [
                                'type' => 'text',
                                'text' => $scheduledFor->format('d/m/Y'),
                            ],
                            [
                                'type' => 'text',
                                'text' => $scheduledFor->format('H:i'),
                            ],
                        ],
                    ]],
                ],
            ],
        ];
    }

    private function buildLogPreviewPayload(array $preview): array
    {
        return [
            'provider' => 'log',
            'request' => [
                'recipient' => $preview['recipient'],
                'body' => $preview['body'],
            ],
        ];
    }
}
