<?php
/**
 * Emissor Oficial de Relatórios Forenses - Front18 SaaS
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['saas_admin']) || $_SESSION['saas_role'] !== 'client') {
    die("Acesso Restrito: Sessão de Cliente Inválida.");
}

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';

/** @var PDO $pdo */
$pdo = Database::getConnection();
$userId = (int)$_SESSION['saas_admin'];
$domainId = (int)($_GET['domain_id'] ?? 0);
$period = $_GET['period'] ?? 'all';

// Validação de Posse do Domínio (Segurança Multitenant)
$stmtCheck = $pdo->prepare("SELECT domain, api_key FROM saas_origins WHERE id = ? AND user_id = ?");
$stmtCheck->execute([$domainId, $userId]);
$origin = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$origin) {
    die("Privilégios Insuficientes: Domínio não pertence a esta conta.");
}

// Filtro de Data
$query = "SELECT * FROM access_logs WHERE client_id = ? "; // No Front18 nosso domain é amarrado via user_id -> client_id. Mas pera, temos vários domínios por user? 
// Originalmente, o SessionManager.php salva siteOrigin!
$query .= "AND siteOrigin = ? ";
$params = [$userId, $origin['domain']];

if ($period !== 'all' && preg_match('/^\d{4}-\d{2}$/', $period)) {
    $query .= "AND DATE_FORMAT(created_at, '%Y-%m') = ? ";
    $params[] = $period;
}

$query .= "ORDER BY id DESC LIMIT 500"; // Limite razoável para um PDF

$stmtLogs = $pdo->prepare($query);
$stmtLogs->execute($params);
$logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

$total = count($logs);
$totalBlocked = array_reduce($logs, function($carry, $item) { return $carry + (strpos($item['action'], 'BLOCKED') !== false ? 1 : 0); }, 0);
$totalAllowed = $total - $totalBlocked;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dossiê Forense - <?= htmlspecialchars($origin['domain']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background: white; color: #0f172a; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .cert-border { border: 2px solid #e2e8f0; padding: 20px; position: relative; }
        .watercolor { position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.03; pointer-events: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="50" x="50" font-size="20" fill="black" text-anchor="middle" transform="rotate(-45 50 50)">Front18 Pro VERIFIED</text></svg>'); }
    </style>
</head>
<body class="p-8 max-w-[210mm] mx-auto font-sans" onload="window.print()">

    <div class="no-print bg-indigo-50 border border-indigo-200 text-indigo-700 p-4 rounded-lg mb-8 flex justify-between items-center shadow-sm">
        <div>
            <h3 class="font-bold"><i class="ph-fill ph-printer"></i> Pronto para Impressão Legal</h3>
            <p class="text-sm">Salve este documento como PDF ou imprima-o para seus arquivos jurídicos.</p>
        </div>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-bold shadow-md transition-all">Imprimir / Salvar PDF</button>
    </div>

    <div class="cert-border rounded-xl">
        <div class="watercolor"></div>
        
        <div class="flex justify-between items-start mb-8 relative z-10 border-b pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white"><i class="ph-bold ph-shield-check text-xl"></i></div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Front18 <span class="text-indigo-600">Pro</span></h1>
                </div>
                <p class="text-xs font-mono text-slate-500 uppercase tracking-widest">Dossiê de Conformidade e Diligência</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">CÓDIGO DE DOCUMENTO</p>
                <code class="text-sm font-mono text-slate-700 bg-slate-100 px-2 py-1 rounded">AG-<?= strtoupper(substr(md5(time().$domainId), 0, 12)) ?></code>
                <p class="text-xs text-slate-500 mt-2">Gerado em: <strong class="text-slate-700"><?= date('d/m/Y H:i:s') ?> UTC</strong></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 relative z-10">
            <div>
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 border-b pb-1">Identificação da Propriedade</h4>
                <p class="text-lg font-bold text-slate-800"><?= htmlspecialchars($origin['domain']) ?></p>
                <p class="text-xs text-slate-500 font-mono mt-1">API Node: <?= htmlspecialchars(substr($origin['api_key'], 0, 10)) ?>...</p>
            </div>
            <div>
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 border-b pb-1">Escopo do Laudo Temporal</h4>
                <p class="text-sm font-bold text-slate-800 mt-1"><?= $period === 'all' ? 'Histórico Integral Completo' : 'Mês: ' . $period ?></p>
                <div class="flex gap-4 mt-2">
                    <span class="text-xs"><strong class="text-emerald-600 text-base"><?= $totalAllowed ?></strong> Legítimos</span>
                    <span class="text-xs"><strong class="text-red-500 text-base"><?= $totalBlocked ?></strong> Bloqueados</span>
                </div>
            </div>
        </div>

        <div class="mb-8 relative z-10">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Isenção de Responsabilidade e Fim Documental</h4>
            <div class="bg-slate-50 border border-slate-200 p-4 rounded text-[11px] leading-relaxed text-slate-600 text-justify">
                Este Dossiê constitui-se única e exclusivamente como um relatório de extração técnica originado do repositório de logs do sistema Front18. Os Hashes Criptográficos listados abaixo operam como verificadores de integridade temporal dos dados coletados durante as respectivas sessões de trânsito (User-Agent/IP/Período), atestando que tais informações foram gravadas no banco de forma contínua e imutável pelo algoritmo de proteção da plataforma, servindo como componente técnico comprobatório de diligência na filtragem ativa de acessos ao domínio.
            </div>
        </div>

        <h4 class="text-[10px] font-bold text-slate-600 uppercase tracking-widest border-b pb-2 mb-4">Cadeia de Custódia (Amostragem Criptográfica Transacional)</h4>
        
        <table class="w-full text-left font-mono text-[9px] relative z-10">
            <thead>
                <tr class="text-slate-500 border-b">
                    <th class="py-2">Data/Hora (UTC)</th>
                    <th>Origem / Client IP (LGPD Masked)</th>
                    <th>Veredito</th>
                    <th>Assinatura SHA-256 da Sessão</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($logs)): ?>
                    <tr><td colspan="4" class="py-6 text-center text-slate-400 italic">Nenhum evento detectado no período especificado.</td></tr>
                <?php else: foreach($logs as $log): ?>
                    <tr class="border-b border-slate-100 items-center">
                        <td class="py-2 text-slate-600"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></td>
                        <td class="text-slate-500"><?= htmlspecialchars($log['ip_address']) ?></td>
                        <td>
                            <?php if(strpos($log['action'], 'BLOCKED') !== false): ?>
                                <span class="text-red-600 font-bold bg-red-50 px-1 py-0.5 rounded">REJEITADO</span>
                            <?php else: ?>
                                <span class="text-emerald-600 font-bold bg-emerald-50 px-1 py-0.5 rounded">AUTORIZADO</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-slate-400 truncate max-w-[150px]"><?= htmlspecialchars($log['current_hash']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <?php if($total > 500): ?>
            <p class="text-center text-[9px] text-slate-400 mt-4 italic">O relatório exibiu as 500 captações mais recentes do período selecionado por limite visual de impressão da amostragem.</p>
        <?php endif; ?>

        <div class="mt-12 text-center text-slate-400 text-[9px] relative z-10 border-t border-slate-200 pt-6">
            <p class="font-bold text-slate-600 uppercase tracking-widest mb-1">Front18 Sistemas de Coleta de Logs</p>
            <p>Relatório Analítico de Extração Automática via Sistema</p>
        </div>

    </div>

</body>
</html>

