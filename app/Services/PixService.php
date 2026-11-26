<?php

namespace App\Services;

class PixService
{
   public static function montarPayload($chave, $nome, $cidade, $txid)
    {
        $payload = '';

        // ID 00 = Payload Format Indicator
        $payload .= self::montarCampo('00', '01');

        // ID 01 = Point of Initiation Method (12 = dinâmico)
        $payload .= self::montarCampo('01', '12');

        // ID 26 = Merchant Account Information
        $gui = self::montarCampo('00', 'BR.GOV.BCB.PIX'); 
        $chave = self::montarCampo('01', $chave);
        $descricao = self::montarCampo('02', $txid);
        $merchantInfo = $gui . $chave . $descricao;

        $payload .= self::montarCampo('26', $merchantInfo);

        // ID 52 = Merchant Category Code
        $payload .= self::montarCampo('52', '0000');

        // ID 53 = Currency (986 = BRL)
        $payload .= self::montarCampo('53', '986');

        // ID 54 = Valor
        //$payload .= self::montarCampo('54', number_format($valor, 2, '.', ''));

        // ID 58 = Country
        $payload .= self::montarCampo('58', 'BR');

        // ID 59 = Nome
        $payload .= self::montarCampo('59', $nome);

        // ID 60 = Cidade
        $payload .= self::montarCampo('60', $cidade);

        // ID 62 = Additional Data Field Template
        $txidField = self::montarCampo('05', $txid);
        $additionalData = $txidField;

        $payload .= self::montarCampo('62', $additionalData);

        // ID 63 = CRC16 (obrigatório)
        $payload .= '6304';

        // Calcular CRC
        $crc = strtoupper(self::crc16($payload));

        return $payload . $crc;
    }

    private static function montarCampo($id, $valor)
    {
        return $id . str_pad(strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }

    private static function crc16($payload)
    {
        $polinomio = 0x1021;
        $result = 0xFFFF;

        $bytes = unpack('C*', $payload);

        foreach ($bytes as $byte) {
            $result ^= ($byte << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($result & 0x8000) {
                    $result = ($result << 1) ^ $polinomio;
                } else {
                    $result = $result << 1;
                }
                $result &= 0xFFFF;
            }
        }

        return dechex($result);
    }
}