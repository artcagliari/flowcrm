<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'owner_id', 'name', 'phone', 'whatsapp', 'email', 'document', 'address', 'city', 'profession', 'origin', 'status', 'notes'];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function leads() { return $this->hasMany(Lead::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function notes() { return $this->hasMany(Note::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
}
