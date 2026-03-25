<?php
/**
 * Arquivo: Crypto.php | Motor de Criptografia Descentralizada e XOR para blindagem de pacotes
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
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
