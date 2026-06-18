<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappConversation extends CompanyModel
{
    use HasFactory;

    protected $table = 'whatsapp_conversations';

    protected $fillable = ['company_id', 'client_id', 'lead_id', 'contact_name', 'phone', 'external_id', 'last_message_at', 'unread_count', 'bot_state'];

    protected $casts = ['last_message_at' => 'datetime', 'unread_count' => 'integer', 'bot_state' => 'array'];

    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function messages() { return $this->hasMany(WhatsappMessage::class, 'conversation_id'); }
    public function latestMessage() { return $this->hasOne(WhatsappMessage::class, 'conversation_id')->latestOfMany(); }
}
