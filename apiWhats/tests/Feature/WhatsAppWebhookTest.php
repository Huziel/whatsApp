<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    private const VERIFY_TOKEN = 'test-whatsapp-verify-token';

    private const APP_SECRET = 'test-meta-app-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.verify_token' => self::VERIFY_TOKEN,
            'services.whatsapp.app_secret' => self::APP_SECRET,
        ]);
    }

    public function test_get_verification_returns_challenge_for_matching_token(): void
    {
        $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token='.self::VERIFY_TOKEN.'&hub.challenge=test-challenge');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertContent('test-challenge');
    }

    public function test_get_verification_rejects_an_incorrect_token(): void
    {
        $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=wrong-token&hub.challenge=test-challenge');

        $response->assertForbidden();
    }

    public function test_post_accepts_a_valid_signature_and_logs_only_safe_event_identifiers(): void
    {
        Log::spy();

        $payload = json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'business-account-123',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'metadata' => ['phone_number_id' => 'phone-id-456'],
                        'messages' => [[
                            'id' => 'wamid.test-789',
                            'from' => '15551234567',
                            'text' => ['body' => 'private message text'],
                        ]],
                    ],
                ]],
            ]],
        ], JSON_UNESCAPED_SLASHES);
        $signature = 'sha256='.hash_hmac('sha256', $payload, self::APP_SECRET);

        $response = $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $payload,
        );

        $response->assertOk()->assertExactJson(['status' => 'ok']);
        $this->assertStringNotContainsString('private message text', $response->getContent());

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(fn ($message, $context) => $message === 'WhatsApp webhook event received'
                && $context === [
                    'event_type' => 'messages',
                    'business_account_id' => 'business-account-123',
                    'phone_number_id' => 'phone-id-456',
                    'message_ids' => ['wamid.test-789'],
                ]);
    }

    public function test_post_rejects_missing_or_invalid_signatures(): void
    {
        $payload = '{"object":"whatsapp_business_account"}';

        $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload,
        )->assertForbidden();

        $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid-signature',
            ],
            $payload,
        )->assertForbidden();
    }
}
