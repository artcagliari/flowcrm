<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesGoal extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'year', 'month', 'target_amount', 'target_deals'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_deals' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
