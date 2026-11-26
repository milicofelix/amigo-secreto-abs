<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    protected $fillable = [
        'group_id',
        'participant_id',
        'cannot_draw_id',
    ];

    // 🔹 Pertence a um grupo
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // 🔹 Participante que não pode tirar outro
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    // 🔹 O participante que ele não pode tirar
    public function cannotDraw()
    {
        return $this->belongsTo(Participant::class, 'cannot_draw_id');
    }
}
