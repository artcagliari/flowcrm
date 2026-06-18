<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = ['webhook_id', 'event', 'payload', 'status', 'response_code', 'response_body'];

    protected $casts = ['payload' => 'array'];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
