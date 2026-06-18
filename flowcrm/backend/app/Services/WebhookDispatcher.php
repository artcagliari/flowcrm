<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;

class WebhookDispatcher
{
    public function dispatch(int $companyId, string $event, array $payload): void
    {
        Webhook::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Webhook $webhook) => in_array($event, $webhook->events ?? [], true) || in_array('*', $webhook->events ?? [], true))
            ->each(function (Webhook $webhook) use ($event, $payload) {
                $delivery = WebhookDelivery::create([
                    'webhook_id' => $webhook->id,
                    'event' => $event,
                    'payload' => $payload,
                    'status' => 'pending',
                ]);

                DeliverWebhook::dispatch($delivery->id);
            });
    }
}
