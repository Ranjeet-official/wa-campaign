<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'template_id',
        'name',
        'message',
        'media_file',
        'sheet_file',
        'start_date',
        'end_date',
        'status',
        'total_contacts',
        'sent_count',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contacts()
    {
        return $this->hasMany(CampaignContact::class);
    }

    public function templates()
    {
        return $this->belongsTo(Template::class,'template_id');
    }
}
