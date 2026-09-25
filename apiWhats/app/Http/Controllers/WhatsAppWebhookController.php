<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub.mode', $request->query('hub_mode'));
        $providedToken = $request->query('hub.verify_token', $request->query('hub_verify_token'));
        $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));
        $configuredToken = config('services.whatsapp.verify_token');

        if (
            $mode !== 'subscribe'
            || ! is_string($configuredToken)
            || $configuredToken === ''
            || ! is_string($providedToken)
            || ! hash_equals($configuredToken, $providedToken)
            || ! is_string($challenge)
        ) {
            return response('Forbidden', 403);
        }

        return response($challenge, 200)->header('Content-Type', 'text/plain');
    }

    public function receive(Request $request)
    {
        $appSecret = config('services.whatsapp.app_secret');
        $signature = $request->header('X-Hub-Signature-256');
        $rawBody = $request->getContent();

        if (! is_string($appSecret) || $appSecret === '' || ! is_string($signature)) {
            return response('Forbidden', 403);
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $rawBody, $appSecret);

        if (! hash_equals($expectedSignature, $signature)) {
            return response('Forbidden', 403);
        }

        $payload = json_decode($rawBody, true);
        $entries = is_array($payload) && is_array($payload['entry'] ?? null)
            ? $payload['entry']
            : [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $changes = is_array($entry['changes'] ?? null) ? $entry['changes'] : [];

            foreach ($changes as $change) {
                if (! is_array($change)) {
                    continue;
                }

                $value = is_array($change['value'] ?? null) ? $change['value'] : [];
                $metadata = is_array($value['metadata'] ?? null) ? $value['metadata'] : [];
                $messageIds = $this->messageIds($value);

                Log::info('WhatsApp webhook event received', [
                    'event_type' => $this->safeIdentifier($change['field'] ?? null)
                        ?? $this->safeIdentifier(is_array($payload) ? ($payload['object'] ?? null) : null)
                        ?? 'unknown',
                    'business_account_id' => $this->safeIdentifier($entry['id'] ?? null),
                    'phone_number_id' => $this->safeIdentifier($metadata['phone_number_id'] ?? null),
                    'message_ids' => $messageIds,
                ]);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function messageIds(array $value): array
    {
        $ids = [];

        foreach (['messages', 'statuses'] as $collection) {
            $items = is_array($value[$collection] ?? null) ? $value[$collection] : [];

            foreach ($items as $item) {
                if (is_array($item) && ($id = $this->safeIdentifier($item['id'] ?? null)) !== null) {
                    $ids[] = $id;
                }

                if (count($ids) >= 20) {
                    return $ids;
                }
            }
        }

        return $ids;
    }

    private function safeIdentifier(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $identifier = (string) $value;

        return preg_match('/\A[A-Za-z0-9._:-]{1,128}\z/', $identifier) === 1
            ? $identifier
            : null;
    }
}
