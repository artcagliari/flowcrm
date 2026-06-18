<?php

namespace App\Services\Whatsapp;

use App\Support\Phone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Fallback provider used when no external WhatsApp API is configured.
 * Messages are logged (not delivered) so the CRM keeps working offline and in tests.
 */
class LogWhatsappProvider implements WhatsappProvider
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function sendText(string $phone, string $body): array
    {
        Log::info('WhatsApp (log provider) outbound message', ['phone' => $phone, 'body' => $body]);

        return [
            'external_id' => 'log_'.Str::uuid(),
            'status' => 'sent',
        ];
    }

    public function parseInbound(array $payload): ?array
    {
        $phone = Phone::normalizeBr(Arr::get($payload, 'phone'));
        if (! $phone) {
            return null;
        }

        return [
            'phone' => $phone,
            'body' => Arr::get($payload, 'body'),
            'media_url' => Arr::get($payload, 'media_url'),
            'external_id' => Arr::get($payload, 'external_id'),
            'contact_name' => Arr::get($payload, 'contact_name'),
        ];
    }
}
