<?php

namespace App\Services\Whatsapp;

use Illuminate\Http\Request;

class MetaWebhookVerifier
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.whatsapp.meta.app_secret'));
    }

    public function verify(Request $request): bool
    {
        $secret = (string) config('services.whatsapp.meta.app_secret');
        $signature = (string) $request->header('X-Hub-Signature-256', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    public function looksLikeMetaPayload(array $payload): bool
    {
        // Meta Cloud API: { "object": "whatsapp_business_account", "entry": [...] }
        // Evolution usa "event" + "data.key.remoteJid" — sem object/entry no top-level.
        return isset($payload['entry']) && is_array($payload['entry'])
            || isset($payload['object']) && is_string($payload['object']);
    }
}
