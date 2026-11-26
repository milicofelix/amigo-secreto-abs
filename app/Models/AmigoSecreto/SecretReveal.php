<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class SecretReveal extends Model
{
     protected $fillable = ['participant_id', 'password_hash', 'revealed_at'];
     protected $casts = [
        'revealed_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public static function verifyPassword($participantId, $password): bool
    {
        $reveal = self::where('participant_id', $participantId)->first();
        return $reveal && Hash::check($password, $reveal->password_hash);
    }
}
