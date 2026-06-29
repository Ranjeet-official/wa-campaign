<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Authenticatable
{
    use HasFactory;

    protected $guard = 'client';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'password',

        'wa_sender_number',
        'wa_api_key',
        'wa_api_url',

        'address',
        'city',
        'state',
        'pincode',

        'status',
        'wa_phone_number_id',
        'wa_access_token',
        'wa_waba_id',

        'chatbot_slug',
        'chatbot_enabled',
        'whatsapp_enabled',
        'welcome_message',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function defaultTemplate()
    {
        return $this->belongsTo(Template::class, 'default_template_id');
    }
}
