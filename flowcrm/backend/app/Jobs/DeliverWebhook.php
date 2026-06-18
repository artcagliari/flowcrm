<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::with('webhook')->find($this->deliveryId);

        if (! $delivery || ! $delivery->webhook) {
            return;
        }

        $webhook = $delivery->webhook;
        $payload = json_encode($delivery->payload);
        $signature = hash_hmac('sha256', $payload, $webhook->secret);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['X-FlowCRM-Signature' => $signature, 'X-FlowCRM-Event' => $delivery->event])
                ->post($webhook->url, $delivery->payload);

            $delivery->update([
                'status' => $response->successful() ? 'delivered' : 'failed',
                'response_code' => $response->status(),
                'response_body' => \Illuminate\Support\Str::limit($response->body(), 2000),
            ]);
        } catch (\Throwable $e) {
            $delivery->update(['status' => 'failed', 'response_body' => $e->getMessage()]);
        }
    }
}
