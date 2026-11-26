<?php

namespace App\Models\Rifas;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RifaNome extends Model
{
    protected $fillable = [
                            'rifa_id',
                            'nome',
                            'user_id',
                            'comprado_em',
                            'comprador_nome',
                            'comprador_whatsapp',
                            'status',
                        ];

    public function rifa()
    {
        return $this->belongsTo(Rifa::class);
    }

    public function comprador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(RifaPagamento::class);
    }

    public function pagamentoAtual()
    {
        return $this->hasOne(RifaPagamento::class)->latestOfMany();
    }
}
