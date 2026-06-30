<?php

namespace App\Services\Whatsapp;

class WhatsappProviderFactory
{
    /**
     * Build a provider from a normalized config array.
     *
     * @param array{provider?: string, evolution?: array, meta?: array} $config
     */
    public function make(array $config): WhatsappProvider
    {
        return match ($config['provider'] ?? 'log') {
            'evolution' => new EvolutionApiProvider($config['evolution'] ?? []),
            'meta' => new MetaCloudApiProvider($config['meta'] ?? []),
            default => new LogWhatsappProvider(),
        };
    }

    /**
     * Translate stored company integration credentials into a provider config array.
     */
    public function fromCredentials(array $credentials): array
    {
        $provider = $credentials['provider'] ?? 'log';

        return [
            'provider' => $provider,
            'evolution' => [
                'base_url' => $credentials['base_url'] ?? null,
                'api_key' => $credentials['api_key'] ?? null,
                'instance' => $credentials['instance'] ?? null,
            ],
            'meta' => [
                'token' => $credentials['token'] ?? null,
                'phone_number_id' => $credentials['phone_number_id'] ?? null,
                'api_version' => $credentials['api_version'] ?? 'v19.0',
            ],
        ];
    }
}
