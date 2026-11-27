<?php

namespace App\Models\AmigoSecreto;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DrawEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'type', 'details'];

    protected $casts = ['details' => 'array'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
