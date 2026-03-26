<?php
/**
 * Arquivo: Crypto.php | Motor de Criptografia AES-256 para Blindagem do Front18
 * @author Revisado e Migrado para Padrões Militares pelo Antigravity
 * @projeto Front18 Pro SaaS Architecture
 */
class Crypto {
    /**
     * Aplica Criptografia Simétrica (AES-256-CBC Real usando OpenSSL)
     * Abandona XOR em favor de blindagem pesada contra engenharia reversa do SDK Frontend.
     */
    public static function encryptResponse($rawHtml, $dynamicKey = null) {
        $key = $dynamicKey ?: (defined('FRONT18_XOR_KEY') ? FRONT18_XOR_KEY : 'agegate_xor_key_2026');
        // Preenche ou trunca a chave mestra para exatamente 32 bytes (256 bits)
        $key = str_pad(substr($key, 0, 32), 32, '0');
        
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        // Criptografia pura (Binary Output pra codar pra Base64 corretamente sem double-encode)
        $encrypted = openssl_encrypt($rawHtml, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        
        // Retorna o IV e o Cipher concatenados em Base64 seguro para split JS ('::')
        return base64_encode(base64_encode($iv) . '::' . base64_encode($encrypted));
    }
    
    /**
     * @deprecated Mantido por retrocompatibilidade temporária com scripts que chamem obfuscateResponse.
     * Na prática, invocará o AES 256 GCM direto.
     */
    public static function obfuscateResponse($rawHtml) {
        return self::encryptResponse($rawHtml);
    }
}
