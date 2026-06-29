<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotPrompt extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'prompt_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}