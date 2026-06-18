<?php

namespace App\Services\Whatsapp;

use App\Support\Phone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiProvider implements WhatsappProvider
{
    public function __construct(private array $config) {}

    public function isConfigured(): bool
    {
        return ! empty($this->config['base_url']) && ! empty($this->config['api_key']) && ! empty($this->config['instance']);
    }

    public function sendText(string $phone, string $body): array
    {
        $response = Http::withHeaders(['apikey' => $this->config['api_key']])
            ->acceptJson()
            ->post(rtrim($this->config['base_url'], '/').'/message/sendText/'.$this->config['instance'], [
                'number' => $phone,
                'text' => $body,
            ]);

        if ($response->failed()) {
            Log::error('Evolution API send failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Evolution API retornou erro '.$response->status().'.');
        }

        return [
            'external_id' => Arr::get($response->json(), 'key.id'),
            'status' => 'sent',
        ];
    }

    public function parseInbound(array $payload): ?array
    {
        $data = Arr::get($payload, 'data', $payload);
        $remoteJid = Arr::get($data, 'key.remoteJid');

        if (! $remoteJid || Arr::get($data, 'key.fromMe')) {
            return null;
        }

        $phone = Phone::normalizeBr(explode('@', $remoteJid)[0]);
        if (! $phone) {
            return null;
        }

        return [
            'phone' => $phone,
            'body' => Arr::get($data, 'message.conversation') ?? Arr::get($data, 'message.extendedTextMessage.text'),
            'media_url' => Arr::get($data, 'message.imageMessage.url') ?? Arr::get($data, 'message.documentMessage.url'),
            'external_id' => Arr::get($data, 'key.id'),
            'contact_name' => Arr::get($data, 'pushName'),
        ];
    }
}
