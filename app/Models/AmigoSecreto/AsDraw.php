<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AsDraw extends Model
{
    protected $fillable = [
        'group_id',
        'drawn_at',
        'status',
        'cancelled_at',
    ];

    protected $casts = [
        'drawn_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'completed')->whereNull('cancelled_at');
    }

    public function events()
    {
        return $this->hasMany(DrawEvent::class, 'draw_id');
    }

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
