<?php 

namespace App\Services;

class PixStatic
{
    public static function gerarPayload($chave, $descricao, $nome, $cidade, $valor, $txid)
    {
        $merchantAccountInfo = self::montarTag('00', 'BR.GOV.BCB.PIX')
            . self::montarTag('01', $chave);

        $gui = self::montarTag('26', $merchantAccountInfo);
        $descricaoTag = $descricao ? self::montarTag('02', $descricao) : '';

        $payload =
            self::montarTag('00', '01') . // Payload Format Indicator
            self::montarTag('01', '12') . // Point of Initiation Method
            $gui .
            self::montarTag('52', '0000') . // Merchant Category Code
            self::montarTag('53', '986') .  // Currency - BRL
            self::montarTag('54', number_format($valor, 2, '.', '')) . // Amount
            self::montarTag('58', 'BR') .    // Country code
            self::montarTag('59', $nome) .   // Merchant name
            self::montarTag('60', $cidade) . // Merchant city
            self::montarTag('62', self::montarTag('05', $txid)); // Additional data

        return $payload . self::montarCRC($payload);
    }

    private static function montarTag($id, $value)
    {
        return $id . sprintf('%02d', strlen($value)) . $value;
    }

    private static function montarCRC($payload)
    {
        $payload .= "6304";
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($payload); $i++) {
            $crc ^= (ord($payload[$i]) << 8);

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = ($crc << 1);
                }
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(dechex($crc));
    }
}
