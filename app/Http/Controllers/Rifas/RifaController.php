<?php

namespace App\Http\Controllers\Rifas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rifas\Rifa;

class RifaController extends Controller
{
    public function index()
    {
        return view('rifas.index', [
            'rifas' => Rifa::all()
        ]);
    }

    public function show(Rifa $rifa)
    {
        $sorteado = null;
        $existeVencedor = false;

        if ($rifa->sorteado_em) {

            // Recupera o nome realmente sorteado
            $sorteado = $rifa->nomes()
                ->orderBy('updated_at', 'desc') // opcional
                ->get()
                ->firstWhere('id', session('ultimo_sorteado_id')); // salvo na sessão no sorteio

            // fallback caso sessão não exista (garantia)
            if(!$sorteado){
                $sorteado = $rifa->nomes()->inRandomOrder()->first();
            }

            // Determina vencedor
            $existeVencedor = (
                $sorteado
                && $sorteado->status === 'pago'
                && !empty($sorteado->comprador_nome)
                && !empty($sorteado->comprador_whatsapp)
            );
        }

        return view('rifas.show', compact('rifa', 'sorteado', 'existeVencedor'));
    }

    public function sortear(Rifa $rifa)
    {
        // Só participam nomes pagos
        $pagos = $rifa->nomes()->where('status', 'pago')->get();

        if ($pagos->count() === 0) {
            return back()->with('error', 'Nenhum número pago foi encontrado. Não é possível sortear.');
        }

        // Sorteio aleatório
        $ganhador = $pagos->random();

        // opcional: salvar na tabela rifas
        $rifa->update([
            'ganhador_nome_id' => $ganhador->id,
            'sorteado_em' => now()
        ]);

        return view('rifas.sorteio', compact('rifa', 'ganhador'));
    }
}
