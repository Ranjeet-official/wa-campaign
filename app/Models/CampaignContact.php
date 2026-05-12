<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignContact extends Model
{
    protected $table = 'campaign_contacts';

    protected $fillable = [
        'campaign_id',
        'name',
        'phone',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
