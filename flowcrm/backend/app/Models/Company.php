<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'owner_name', 'legal_name', 'document', 'profession', 'email', 'phone', 'whatsapp',
        'address', 'city', 'state', 'zip_code', 'logo', 'logo_path', 'primary_color', 'status', 'plan_name',
        'max_users', 'assignment_mode', 'last_assigned_user_id', 'stripe_customer_id', 'profession_mode', 'starts_at', 'expires_at', 'notes',
    ];

    protected $casts = ['starts_at' => 'date', 'expires_at' => 'date', 'max_users' => 'integer'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['role_id', 'role', 'is_owner', 'status'])->withTimestamps();
    }

    public function pipelines() { return $this->hasMany(Pipeline::class); }
    public function subscription() { return $this->hasOne(Subscription::class)->latestOfMany(); }
    public function integrations() { return $this->hasMany(CompanyIntegration::class); }
}
