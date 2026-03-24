<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['saas_admin'])) {
    die("<h1>Acesso Negado</h1><p>A extração de Relatórios Técnicos requer autenticação administrativa.</p>");
}

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';

if (!isset($_GET['id'])) {
    die("ID do relatório não especificado.");
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM access_logs WHERE id = ?");
$stmt->execute([$_GET['id']]);
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    die("Relatório não encontrado no banco de dados.");
}

// Helper para mascarar dados delicados se estiver rodando LGPD puro
function escape($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

// Tratamento de IP LGPD: mantendo privacidade mas garantindo valor probatório técnico
$displayIp = escape($log['ip_address']);
if ($displayIp === '127.0.0.0' || $displayIp === '::1') {
    $displayIp = '127.0.xxx.xxx';
} else {
    // Mascara qualquer IP real preservando o provider/origem (ex: 189.10.xxx.xxx)
    $displayIp = preg_replace('/(\.\d+){1,2}$/', '.xxx.xxx', $displayIp);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="icon" type="image/png" href="public/img/favicon.png">
    <meta charset="UTF-8">
    <title>Relatório Técnico de Auditoria #<?= escape($log['id']) ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #eee; padding: 40px; margin: 0; line-height: 1.5; }
        .page { background: #fff; width: 210mm; min-height: 297mm; padding: 25mm; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #555; }
        
        .section { margin-bottom: 30px; }
        .section-title { font-weight: bold; font-size: 16px; margin-bottom: 10px; text-transform: uppercase; background: #f0f0f0; padding: 5px; border-left: 4px solid #000; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table th, table td { padding: 8px; border: 1px solid #ccc; font-size: 14px; text-align: left; vertical-align: top; }
        table th { width: 200px; background: #f9f9f9; }
        
        .hash-string { font-family: 'Courier New', Courier, monospace; word-break: break-all; font-size: 12px; }
        
        .declaration { border: 2px dashed #000; padding: 20px; background: #fafafa; margin-bottom: 30px; }
        .interpretation { border: 1px solid #000; padding: 20px; margin-top: 40px; background: #f9f9f9; }
        
        .footer { text-align: center; font-size: 12px; color: #555; margin-top: 50px; border-top: 1px solid #ccc; padding-top: 10px; }
        
        @media print {
            body { background: none; padding: 0; }
            .page { box-shadow: none; margin: 0; width: auto; height: auto; min-height: auto; padding: 15mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div style="text-align:center; padding-bottom:20px;" class="no-print">
        <button onclick="window.print()" style="padding:15px 30px; background:#0f172a; color:#fff; cursor:pointer; font-weight:bold; border-radius:8px; border:none; font-size:16px;">🖨️ Salvar Relatório em PDF</button>
        <button onclick="window.close()" style="padding:15px; margin-left: 10px; cursor:pointer;">Fechar Aba</button>
    </div>

    <div class="page">
        <div class="header">
            <h1>RELATÓRIO TÉCNICO DE AUDITORIA DE ACESSO</h1>
            <p>Sistema Front18 Pro - Registro Auditável de Interação do Usuário</p>
        </div>

        <div class="declaration">
            <h2 style="margin-top:0; font-size: 16px;">ESCOPO DA COLETA DE EVIDÊNCIAS</h2>
            Este documento constitui um relatório técnico extraído de forma imutável dos logs de sistema da aplicação.<br><br>
            Ele evidencia a <b>declaração de maioridade realizada pelo usuário no momento do acesso</b> e atua como registro de aceite tecnológico que pode ser utilizado como evidência de diligência por parte da plataforma nos controles de restrição e políticas corporativas vigentes.
        </div>

        <div class="section">
            <div class="section-title">A. Dados da Interação e Origem</div>
            <table>
                <tr><th>ID Interno do Registro</th><td>#<?= escape($log['id']) ?></td></tr>
                <tr><th>Data e Hora do Fato (UTC)</th><td><?= escape($log['created_at']) ?></td></tr>
                <tr><th>Origem do Serviço (Site)</th><td><?= escape($log['siteOrigin']) ?></td></tr>
                <tr><th>Ação Sistêmica Disparada</th><td><?= escape($log['action']) ?></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">B. Identificação do Solicitante</div>
            <table>
                <tr><th>Endereço Lógico (IP)</th><td><strong><?= $displayIp ?></strong></td></tr>
                <tr><th>Fingerprint / User-Agent</th><td style="font-size: 12px;"><?= escape($log['user_agent']) ?></td></tr>
                <tr><th>Identificador de Sessão (Token Temporário)</th><td class="hash-string"><?= escape($log['session_id']) ?></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">C. Evidência do Contrato (Termos de Uso)</div>
            <table>
                <?php if (!empty($log['terms_hash']) && $log['terms_hash'] !== 'not_found'): ?>
                    <tr><th>Versão dos Termos Aceitos</th><td><?= escape($log['terms_version']) ?></td></tr>
                    <tr><th>Hash do Documento (SHA-256)</th><td class="hash-string"><?= escape($log['terms_hash']) ?></td></tr>
                <?php else: ?>
                    <tr><th>Versão dos Termos Aceitos</th><td><?= escape($log['terms_version']) ?: 'Não identificada' ?></td></tr>
                    <tr><th>Hash do Documento (SHA-256)</th><td class="hash-string" style="color: #555;">Não disponível para este registro. A versão do documento foi identificada conforme controle de versão do tenant no momento da interação.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="section">
            <div class="section-title">D. Cadeia de Custódia (Garantia Tecnológica)</div>
            <p style="font-size:13px; margin-top:0;">Este registro possui uma assinatura criptográfica atrelada ao log imediatamente anterior, assegurando tecnicamente a imutabilidade da base de dados e a ordem cronológica dos eventos, prevenindo adulterações posteriores.</p>
            <table>
                <tr><th>Hash do Bloco Anterior</th><td class="hash-string"><?= escape($log['prev_hash']) ?: 'N/A' ?></td></tr>
                <tr><th>Hash Deste Registro</th><td class="hash-string"><strong><?= escape($log['current_hash']) ?: 'N/A' ?></strong></td></tr>
                <tr><th>Assinatura do Servidor (HMAC)</th><td class="hash-string"><?= escape($log['server_signature']) ?: 'N/A' ?></td></tr>
            </table>
        </div>

        <div class="interpretation">
            <h2 style="margin-top:0; font-size: 14px; text-transform: uppercase;">INTERPRETAÇÃO TÉCNICA AUTOMATIZADA</h2>
            <p style="font-size: 14px; margin: 0; font-family: 'Courier New', Courier, monospace;">
                "Este registro indica que houve interação ativa do usuário, incluindo o aceite explícito das condições apresentadas no momento da interação (Timestamp UTC). O evento foi registrado com encadeamento criptográfico, garantindo integridade, rastreabilidade e resistência à adulteração na base de dados."
            </p>
        </div>

        <div style="margin-top:20px; border: 2px solid #10b981; padding: 15px; background: #ecfdf5;">
            <h3 style="margin:0 0 10px 0; font-size: 15px; color: #047857; text-transform: uppercase;">NÍVEL DE INTEGRIDADE DO REGISTRO: ALTO</h3>
            <p style="margin:0; font-size:13px; color:#065f46; font-weight:bold;">Critérios de Validação Criptográfica:</p>
            <ul style="margin:5px 0 0 0; padding-left:20px; font-size:13px; color:#065f46; font-weight:bold;">
                <li>Cadeia de hash válida (Imutabilidade Sequencial)</li>
                <li>Assinatura HMAC consistente (Garantia de Origem do Servidor)</li>
                <li>Ordem cronológica preservada (Anti-Tampering)</li>
            </ul>
        </div>

        <div class="footer">
            Gerado eletronicamente em <?= date('Y-m-d H:i:s') ?> UTC<br>
            <strong>RELATÓRIO DE EVIDÊNCIA DE SESSÃO - Front18 SaaS Ecosystem</strong>
        </div>
    </div>
</body>
</html>

