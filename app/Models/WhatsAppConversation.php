<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'phone_number', 'contact_name', 'last_message',
        'last_message_at', 'unread_count', 'is_archived'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_archived' => 'boolean',
        'unread_count' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function getContactDisplayAttribute()
    {
        return $this->contact_name ?? $this->phone_number;
    }
}
