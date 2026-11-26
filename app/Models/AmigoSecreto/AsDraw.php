<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;

class AsDraw extends Model
{
    protected $fillable = [
        'group_id',
        'drawn_at',
        'status',
    ];

    protected $casts = [
    'drawn_at' => 'datetime',
    ];

    // 🔹 Pertence a um grupo
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // 🔹 Possui várias atribuições (quem tirou quem)
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'draw_id');
    }
}
