<?php

namespace App\Http\Controllers\Rifas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rifas\RifaNome;
use App\Services\PixService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CompraController extends Controller
{
   public function comprar(Request $request, RifaNome $nome)
    {
        if ($nome->status !== 'disponivel') {
            return back()->with('error', 'Este nome já foi reservado ou pago.');
        }

        $request->validate([
            'comprador_nome' => 'required|string|max:255',
            'comprador_whatsapp' => 'required|string|max:20',
        ]);

       $nome->update([
            'comprador_nome'      => $request->comprador_nome,
            'comprador_whatsapp'  => $request->comprador_whatsapp,
            'comprado_em'         => now(),
            'status'              => 'reservado',
        ]);

        return redirect()->route('rifas.pix', $nome->id);
    }

    // public function pix(RifaNome $nome)
    // {
    //     $rifa = $nome->rifa;

    //     $result = PixStatic::gerar(
    //         chave: '11951410140',                     // SUA CHAVE PIX
    //         nome: 'ADRIANO FELIX',                    // Nome que aparece no PIX
    //         cidade: 'SAO PAULO',                      // Cidade obrigatória
    //         valor: $rifa->valor,
    //         identificador: 'NOME-' . $nome->id
    //     );

    //     return view('rifas.pix', [
    //         'nome' => $nome,
    //         'qrcode' => $result['svg'],
    //         'payload' => $result['payload'],
    //     ]);
    // }
    public function pix(RifaNome $nome)
    {
        $rifa = $nome->rifa;

        // IMPORTANTE: chave celular deve ter "+" na frente
        $chave = '+5511959607634';

        $payload = PixService::montarPayload(
            chave: $chave,
            nome: 'SHISLENE RICOSTI',
            cidade: 'SAO PAULO',
            //valor: $rifa->valor,
            txid: 'NOME-'.$nome->id
        );

        // QRCode
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 6,
        ]);

        $qrcode = (new QRCode($options))->render($payload);

        return view('rifas.pix', compact('nome','payload','qrcode'));
    }
}
