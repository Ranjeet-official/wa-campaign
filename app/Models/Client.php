<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'email',
        'phone',
        'company',

        'wa_sender_number',
        'wa_api_key',
        'wa_api_url',

        'address',
        'city',
        'state',
        'pincode',

        'status',
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
