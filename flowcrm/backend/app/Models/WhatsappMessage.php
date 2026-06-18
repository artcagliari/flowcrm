<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = ['conversation_id', 'direction', 'body', 'sensitivity_level', 'media_url', 'status', 'external_id', 'sent_by_user_id', 'error'];

    public function conversation() { return $this->belongsTo(WhatsappConversation::class, 'conversation_id'); }
    public function sender() { return $this->belongsTo(User::class, 'sent_by_user_id'); }
}
