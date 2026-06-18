<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\Whatsapp\WhatsappProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $messageId) {}

    public function handle(WhatsappProvider $provider): void
    {
        $message = WhatsappMessage::with('conversation')->find($this->messageId);

        if (! $message || ! $message->conversation) {
            return;
        }

        try {
            $result = $provider->sendText($message->conversation->phone, (string) $message->body);
            $message->update([
                'status' => $result['status'] ?? 'sent',
                'external_id' => $result['external_id'] ?? null,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $message->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        WhatsappMessage::where('id', $this->messageId)->update(['status' => 'failed', 'error' => $e->getMessage()]);
    }
}
