<?php

namespace App\Services\Whatsapp;

use App\Support\Phone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCloudApiProvider implements WhatsappProvider
{
    public function __construct(private array $config) {}

    public function isConfigured(): bool
    {
        return ! empty($this->config['token']) && ! empty($this->config['phone_number_id']);
    }

    public function sendText(string $phone, string $body): array
    {
        $version = $this->config['api_version'] ?? 'v19.0';
        $response = Http::withToken($this->config['token'])
            ->acceptJson()
            ->post("https://graph.facebook.com/{$version}/{$this->config['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $body],
            ]);

        if ($response->failed()) {
            Log::error('Meta Cloud API send failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Meta WhatsApp Cloud API retornou erro '.$response->status().'.');
        }

        return [
            'external_id' => Arr::get($response->json(), 'messages.0.id'),
            'status' => 'sent',
        ];
    }

    public function parseInbound(array $payload): ?array
    {
        $value = Arr::get($payload, 'entry.0.changes.0.value');
        $message = Arr::get($value, 'messages.0');

        if (! $message) {
            return null;
        }

        $phone = Phone::normalizeBr(Arr::get($message, 'from'));
        if (! $phone) {
            return null;
        }

        return [
            'phone' => $phone,
            'body' => Arr::get($message, 'text.body'),
            'media_url' => Arr::get($message, 'image.link') ?? Arr::get($message, 'document.link'),
            'external_id' => Arr::get($message, 'id'),
            'contact_name' => Arr::get($value, 'contacts.0.profile.name'),
        ];
    }
}
