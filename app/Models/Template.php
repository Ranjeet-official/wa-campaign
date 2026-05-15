<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'language',
        'message',
        'media_file',
        'status',
    ];
}
