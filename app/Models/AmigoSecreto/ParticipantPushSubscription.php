<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class ParticipantPushSubscription extends Model
{
    protected $fillable = ['participant_id', 'endpoint_hash', 'subscription', 'last_notified_draw_id'];

    protected $casts = ['subscription' => 'encrypted:array'];
}
