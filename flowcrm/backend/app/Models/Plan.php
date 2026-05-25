<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'monthly_price', 'max_users', 'features'];

    protected $casts = ['monthly_price' => 'decimal:2', 'features' => 'array'];
}
