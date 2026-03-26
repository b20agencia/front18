<?php
/**
 * DB Auto-Migration Tool
 * Atualiza o MySQL do cPanel com as novas colunas Biométricas, UX, e Hash do Blockchain.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';

try {
    Database::setup();
    echo "<h1>✅ SUCESSO ABSOLUTO!</h1>";
    echo "<p>Seu banco de dados Oficial (cPanel) foi atualizado com todas as colunas neurais e blocos de segurança.</p>";
    echo "<p>Agora o <b>track.php</b> consegue ler a opção <i>age_estimation_active</i> do Painel e o <b>SessionManager</b> consegue salvar o <i>kyc_cpf_mask</i>.</p>";
    echo "<p><br><b>Pode voltar para o seu Laboratório e realizar a validação que a Câmera vai disparar lindamente!</b></p>";
} catch (Exception $e) {
    echo "<h1>❌ ERRO DE MIGRAÇÃO</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
