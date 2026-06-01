<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'description', 'amount', 'category', 'due_date', 'paid_at', 'payment_method', 'status', 'notes'];

    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date', 'paid_at' => 'date'];
}
