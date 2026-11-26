<?php

namespace App\Models\Rifas;

use Illuminate\Database\Eloquent\Model;

class RifaPagamento extends Model
{
    protected $fillable = [
        'rifa_nome_id',
        'comprador_nome',
        'comprador_whatsapp',
        'valor',
        'status',
        'gateway_reference',
        'pix_qr_code',
        'pix_qr_code_base64',
        'paid_at',
    ];

    public function nome()
    {
        return $this->belongsTo(RifaNome::class, 'rifa_nome_id');
    }
}
