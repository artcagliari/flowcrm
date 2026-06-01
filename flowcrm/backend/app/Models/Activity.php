<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'client_id', 'lead_id', 'subject_type', 'subject_id', 'action', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
}
