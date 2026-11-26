<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
     public $timestamps = false;

    protected $fillable = [
        'participant_id',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

}
