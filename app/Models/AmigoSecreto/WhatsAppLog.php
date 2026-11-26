<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class WhatsAppLog extends Model
{
    protected $table = 'whats_app_logs';
    protected $fillable = [
        'phone', 'message', 'status', 'response', 'error_message',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}
