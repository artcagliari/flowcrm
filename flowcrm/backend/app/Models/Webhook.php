<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Webhook extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'url', 'events', 'secret', 'is_active'];

    protected $casts = ['events' => 'array', 'is_active' => 'boolean'];

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
