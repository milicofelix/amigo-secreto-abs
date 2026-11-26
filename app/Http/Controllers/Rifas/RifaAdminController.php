<?php

namespace App\Http\Controllers\Rifas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rifas\RifaNome;
use App\Models\Rifas\Rifa;

class RifaAdminController extends Controller
{
    public function pendentes()
    {
        $pendentes = RifaNome::where('status', 'reservado')->get();
        return view('rifas.admin.pendentes', compact('pendentes'));
    }

    public function confirmar(RifaNome $nome)
    {
        $nome->update(['status' => 'pago']);
        return back()->with('success', 'Pagamento confirmado!');
    }

    //     public function confirmarPagamento(RifaNome $nome)
    // {
    //     $nome->update(['status' => 'pago']);

    //     return redirect()->route('rifas.show', $nome->rifa_id)
    //                     ->with('success', 'Pagamento confirmado com sucesso!');
    // }

    public function sortear(Rifa $rifa)
    {
        // 1 — BLOQUEIA NOVO SORTEIO
        if ($rifa->sorteado_em) {
            return back()->with('error', 'Esta rifa já foi sorteada.');
        }

        // Sorteia SEMPRE entre todos os nomes da cartela
        $todos = $rifa->nomes()->get();

        if ($todos->count() === 0) {
            return back()->with('error', 'Nenhum nome encontrado na cartela.');
        }

        // Nome sorteado (pode ou não ter comprador)
        $sorteado = $todos->random();

        // Existe vencedor?
        $existeVencedor = $sorteado->status === 'pago';

        if ($existeVencedor) {
            // Salva informações do vencedor
            $rifa->update([
                'ganhador_nome_sorteado' => $sorteado->nome, // nome da cartela sorteado
                'ganhador_nome'          => $sorteado->comprador_nome,
                'ganhador_whatsapp'      => $sorteado->comprador_whatsapp,
                'sorteado_em'            => now(),
            ]);
        } else {
            // Salva apenas o nome sorteado, sem ganhador
            $rifa->update([
                'ganhador_nome_sorteado' => $sorteado->nome,
                'ganhador_nome'          => null,
                'ganhador_whatsapp'      => null,
                'sorteado_em'            => now(),
            ]);
        }

        return view('rifas.admin.resultado', [
            'rifa' => $rifa->fresh(),
            'sorteado' => $sorteado,
            'existeVencedor' => $existeVencedor
        ]);
    }

    // public function resultado(Rifa $rifa)
    // {
    //     return view('rifas.admin.resultado', [
    //         'rifa' => $rifa,
    //         'ganhador' => null, // opcional
    //     ]);
    // }

    // public function sortear(Rifa $rifa)
    // {
    //     $pagos = $rifa->nomes()
    //         ->where('status', 'pago')
    //         ->whereNotNull('comprador_nome')
    //         ->whereNotNull('comprador_whatsapp')
    //         ->get();

    //     // 👉 Caso não haja vencedores, grava "sem vencedor"
    //     if ($pagos->count() === 0) {

    //         $rifa->update([
    //             'ganhador_numero'   => null,
    //             'ganhador_nome'     => null,
    //             'ganhador_whatsapp' => null,
    //             'sorteado_em'       => now(),
    //         ]);

    //         return view('rifas.admin.resultado', [
    //             'rifa' => $rifa,
    //             'ganhador' => null
    //         ]);
    //     }

    //     // Sorteio normal
    //     $ganhador = $pagos->random();

    //     $rifa->update([
    //         'ganhador_numero'   => $ganhador->nome,
    //         'ganhador_nome'     => $ganhador->comprador_nome,
    //         'ganhador_whatsapp' => $ganhador->comprador_whatsapp,
    //         'sorteado_em'       => now(),
    //     ]);

    //     return view('rifas.admin.resultado', compact('rifa', 'ganhador'));
    // }

    // public function sorteioAnimado(Rifa $rifa)
    // {
    //     $todos = $rifa->nomes;

    //     if ($todos->count() === 0) {
    //         return back()->with('error', 'Nenhum nome cadastrado.');
    //     }

    //     $sorteado = $todos->random();

    //     // Verifica se esse sorteado tem vencedor
    //     $existeVencedor = (
    //         $sorteado->status === 'pago'
    //         && !empty($sorteado->comprador_nome)
    //         && !empty($sorteado->comprador_whatsapp)
    //     );

    //     // Salva apenas o ID do nome sorteado na sessão
    //     session([
    //         'rifa_sorteada_id' => $rifa->id,
    //         'nome_sorteado_id' => $sorteado->id,
    //         'existe_vencedor'  => $existeVencedor,
    //         'ultimo_sorteado_id' => $sorteado->id
    //     ]);

    //     return view('rifas.admin.sorteio-animado', [
    //         'rifa' => $rifa,
    //         'nomes' => $todos,
    //         'sorteado' => $sorteado,
    //         'existeVencedor' => $existeVencedor,
    //     ]);
    // }

    public function sorteioAnimado(Rifa $rifa)
    {
        // Já sorteada?
        if ($rifa->status === 'sorteada') {
            return redirect()->route('admin.rifas.resultado', $rifa->id);
        }

        $todos = $rifa->nomes;

        if ($todos->isEmpty()) {
            return back()->with('error', 'Nenhum nome cadastrado.');
        }

        // SORTEIA UMA ÚNICA VEZ
        $sorteado = $todos->random();

        // Verificar vencedor
        $existeVencedor = (
            $sorteado->status === 'pago'
            && !empty($sorteado->comprador_nome)
            && !empty($sorteado->comprador_whatsapp)
        );

        // SALVAR NO BANCO – DEFINITIVO
        $rifa->update([
            'status'           => 'sorteada',
            'sorteado_em'      => now(),
            'sorteado_nome_id' => $sorteado->id
        ]);

        // Enviar dados pra animação
        return view('rifas.admin.sorteio-animado', [
            'rifa' => $rifa,
            'nomes' => $todos,
            'sorteado' => $sorteado,
            'existeVencedor' => $existeVencedor,
        ]);
    }

    public function salvarGanhador(Request $request, Rifa $rifa)
    {
        $request->validate([
            'id' => 'required|exists:rifa_nomes,id',
        ]);

        $ganhador = RifaNome::find($request->id);

        $rifa->update([
            'ganhador_numero'   => $ganhador->nome,
            'ganhador_nome'     => $ganhador->comprador_nome,
            'ganhador_whatsapp' => $ganhador->comprador_whatsapp,
            'sorteado_em'       => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

   public function resultado(Rifa $rifa, $ganhador = null)
    {
        $sorteado = null;
        $existeVencedor = false;

        if ($ganhador) {
            $sorteado = RifaNome::find($ganhador);

            if ($sorteado) {
                $existeVencedor = (
                    $sorteado->status === 'pago' &&
                    !empty($sorteado->comprador_nome)
                );
            }
        }

        return view('rifas.admin.resultado', compact('rifa', 'sorteado', 'existeVencedor'));
    }
}
