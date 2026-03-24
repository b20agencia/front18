<?php
/**
 * Motor Criptográfico Leve de Injeção em Rede (XOR Engine)
 */
class Crypto {
    /**
     * Aplica máscara XOR para ocultar Payload HTML do Network Tab (DevTools)
     * e engole com Base64 para garantir a string no JSON.
     */
    public static function obfuscateResponse($rawHtml) {
        $key = FRONT18_XOR_KEY;
        $xorCipher = '';
        for($i = 0; $i < strlen($rawHtml); $i++) {
            $xorCipher .= $rawHtml[$i] ^ $key[$i % strlen($key)];
        }
        return base64_encode($xorCipher);
    }
}
