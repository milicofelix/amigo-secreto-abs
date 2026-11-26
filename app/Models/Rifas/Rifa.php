<?php

namespace App\Models\Rifas;

use Illuminate\Database\Eloquent\Model;

class Rifa extends Model
{
    protected $fillable = [
                            'titulo', 
                            'premio', 
                            'valor', 
                            'sorteado_em', 
                            'status', 
                            'sorteado_nome_id', 
                        ];

    public function nomes()
    {
        return $this->hasMany(RifaNome::class);
    }

    public function ganhador()
    {
        return $this->belongsTo(RifaNome::class, 'ganhador_nome_id');
    }

     public function nomeSorteado()
    {
        return $this->belongsTo(RifaNome::class, 'sorteado_nome_id');
    }
}
