<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'client_id', 'lead_id', 'content', 'body', 'type', 'is_private'];

    protected $casts = ['is_private' => 'boolean'];

    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
