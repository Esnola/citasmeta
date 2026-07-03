<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        abort_unless($this->isValidVerificationRequest($request), SymfonyResponse::HTTP_FORBIDDEN);

        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge', ''));

        return response($challenge, SymfonyResponse::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    public function handle(Request $request, AppointmentDeliveryStatusSyncer $syncer): Response
    {
        abort_unless($this->hasValidSignature($request), SymfonyResponse::HTTP_FORBIDDEN);

        if (! $this->matchesConfiguredAccount($request)) {
            return response()->noContent();
        }

        foreach ($this->statuses($request->all()) as $status) {
            $syncer->syncFromProviderWebhook(
                'cloud_api',
                (string) ($status['id'] ?? ''),
                (string) ($status['status'] ?? ''),
                $request->all()
            );
        }

        return response()->noContent();
    }

    private function isValidVerificationRequest(Request $request): bool
    {
        $verifyToken = (string) config('whatsapp.meta.verify_token', config('whatsapp.webhook.verify_token', ''));
        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode', ''));
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge', ''));
        $incomingToken = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token', ''));

        return $verifyToken !== ''
            && $mode === 'subscribe'
            && hash_equals($verifyToken, $incomingToken)
            && $challenge !== '';
    }

    private function hasValidSignature(Request $request): bool
    {
        $appSecret = (string) config('whatsapp.meta.app_secret', config('whatsapp.webhook.app_secret', ''));
        $signature = (string) $request->header('X-Hub-Signature-256', '');

        if ($appSecret === '' || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals('sha256='.$expected, $signature);
    }

    private function matchesConfiguredAccount(Request $request): bool
    {
        $configuredWabaId = (string) config('whatsapp.meta.waba_id', config('whatsapp.webhook.waba_id', ''));

        if ($configuredWabaId === '') {
            return true;
        }

        return collect((array) $request->input('entry', []))
            ->contains(fn (array $entry): bool => (string) ($entry['id'] ?? '') === $configuredWabaId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statuses(array $payload): array
    {
        $statuses = [];

        foreach ((array) data_get($payload, 'entry', []) as $entry) {
            foreach ((array) data_get($entry, 'changes', []) as $change) {
                foreach ((array) data_get($change, 'value.statuses', []) as $status) {
                    if (is_array($status)) {
                        $statuses[] = $status;
                    }
                }
            }
        }

        return $statuses;
    }
}
