<?php 

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class PixStatic
{
    public static function gerar($chave, $nome, $cidade, $valor, $identificador)
    {
        $valor = number_format($valor, 2, '.', '');

        $payload =
            '000201' .
            '010212' .
            '26' . sprintf('%02d', strlen('0014BR.GOV.BCB.PIX01' . strlen($chave) . $chave)) .
            '0014BR.GOV.BCB.PIX' .
            '01' . sprintf('%02d', strlen($chave)) . $chave .
            '52040000' .
            '5303986' .
            '540' . sprintf('%02d', strlen($valor)) . $valor .
            '5802BR' .
            '59' . sprintf('%02d', strlen($nome)) . $nome .
            '60' . sprintf('%02d', strlen($cidade)) . $cidade .
            '62' . sprintf('%02d', strlen('05' . sprintf('%02d', strlen($identificador)) . $identificador)) .
            '05' . sprintf('%02d', strlen($identificador)) . $identificador;

        $crc = self::crc16($payload . '6304');
        $payload .= '6304' . $crc;

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_L,
        ]);

        $qrcode = (new QRCode($options))->render($payload);

        return [
            'payload' => $payload,
            'svg'     => $qrcode,
        ];
    }

    private static function crc16($payload)
    {
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        for ($i = 0; $i < strlen($payload); $i++) {
            $resultado ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($resultado & 0x8000) != 0) {
                    $resultado = ($resultado << 1) ^ $polinomio;
                } else {
                    $resultado <<= 1;
                }
                $resultado &= 0xFFFF;
            }
        }

        return strtoupper(dechex($resultado));
    }
}
