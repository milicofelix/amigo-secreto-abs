<?php 

namespace App\Helpers;

use App\Models\AmigoSecreto\AccessLog;
use Illuminate\Support\Facades\Request;

class AccessHelper
{
    public static function log($participantId = null)
    {
        try {
            AccessLog::create([
                'participant_id' => $participantId,
                'ip_address'     => Request::ip(),
                'user_agent'     => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            // Evita crash caso tabela não exista
        }
    }
}
