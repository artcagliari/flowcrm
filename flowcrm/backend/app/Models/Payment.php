<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'client_id', 'user_id', 'description', 'amount', 'category', 'due_date', 'paid_at', 'payment_method', 'status', 'notes'];

    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date', 'paid_at' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
}
