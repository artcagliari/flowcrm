<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'plan_id', 'status', 'starts_at', 'ends_at', 'stripe_subscription_id', 'stripe_status'];

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
