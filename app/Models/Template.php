<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory;

    protected $table = 'templates';

    protected $fillable = [
        'client_id',
        'name',
        'language',
        'category',
        'message',
        'variables',
        'meta_template_id',
        'status',
        'approved_at'
    ];

    protected $casts = [
        'variables'   => 'array',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Check if template is approved
    // public function isApproved()
    // {
    //     return $this->status === 'approved';
    // }

    // // Check if template is pending
    // public function isPending()
    // {
    //     return $this->status === 'pending';
    // }

    // // Check if template is rejected
    // public function isRejected()
    // {
    //     return $this->status === 'rejected';
    // }

    // // Get formatted message (for campaign use)
    // public function getFormattedMessage(array $values = [])
    // {
    //     $message = $this->message;

    //     foreach ($values as $key => $value) {
    //         $message = str_replace('{{'.($key+1).'}}', $value, $message);
    //     }

    //     return $message;
    // }

    // // Extract variables from message (helper)
    // public static function extractVariables($message)
    // {
    //     preg_match_all('/{{\d+}}/', $message, $matches);
    //     return array_values(array_unique($matches[0]));
    // }
}
