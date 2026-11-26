<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'draw_id',
        'giver_id',
        'receiver_id',
    ];

    // 🔹 Pertence a um sorteio
    public function draw()
    {
        return $this->belongsTo(AsDraw::class);
    }

    // 🔹 Quem deu o presente
    public function giver()
    {
        return $this->belongsTo(Participant::class, 'giver_id');
    }

    // 🔹 Quem recebeu o presente
    public function receiver()
    {
        return $this->belongsTo(Participant::class, 'receiver_id');
    }
}
