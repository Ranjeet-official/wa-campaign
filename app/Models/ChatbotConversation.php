<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $connection = 'chatbot';
    protected $table = 'chat_histories';

    protected $fillable = [
        'client_id',
        'session_id',
        'user_name',
        'user_email',
        'user_phone',
        'sender',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}