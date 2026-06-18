<?php

namespace App\Services\Whatsapp;

interface WhatsappProvider
{
    /**
     * Send a text message to a normalized phone number.
     *
     * @return array{external_id: ?string, status: string} Result with provider message id and status.
     */
    public function sendText(string $phone, string $body): array;

    /**
     * Parse a provider webhook payload into a normalized inbound message array, or null if not a message event.
     *
     * @return array{phone: string, body: ?string, media_url: ?string, external_id: ?string, contact_name: ?string}|null
     */
    public function parseInbound(array $payload): ?array;

    /** Whether the provider has the credentials required to actually reach the API. */
    public function isConfigured(): bool;
}
