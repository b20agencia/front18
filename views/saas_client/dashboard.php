<?php
/**
 * Arquivo: dashboard.php | UI Painel B2B do Cliente Final Assinante (Análise de Gráficos e Configurações API)
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['saas_admin']) || $_SESSION['saas_role'] !== 'client') {
    header("Location: ?route=login");
    exit;
}

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';

$pdo = Database::getConnection();
$userId = $_SESSION['saas_admin'];

// Sair da Impersonação (Super Admin retorna ao Admin Dashboard)
if (isset($_GET['cancel_impersonate']) && isset($_SESSION['superadmin_backup_id'])) {
    $_SESSION['saas_admin'] = $_SESSION['superadmin_backup_id'];
    $_SESSION['saas_role'] = 'superadmin';
    unset($_SESSION['superadmin_backup_id']);
    header("Location: ?route=admin");
    exit;
}

// Recuperar Dados do Cliente para mostrar na UI e validar Trial
$stmtUser = $pdo->prepare("SELECT * FROM saas_users WHERE id = ? LIMIT 1");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$demoName = $user ? explode('@', $user['email'])[0] : "Cliente B2B";

// Recuperar Configuração de Domínio do Cliente baseada no Blueprint
$stmtOrigin = $pdo->prepare("SELECT * FROM saas_origins WHERE user_id = ? LIMIT 1");
$stmtOrigin->execute([$userId]);
$config = $stmtOrigin->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../src/Core/DashboardActions.php';
DashboardActions::handle($pdo, $userId);

// Recupera o Plano Oficial do Usuário no Banco (Para Renderização da View)
$stmtPlan = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
$stmtPlan->execute([$user['plan_id'] ?? 1]); // Padrão Plan ID 1 se não setado
$planDetails = $stmtPlan->fetch(PDO::FETCH_ASSOC);

if (!$planDetails) {
    // Fallback de Emergência
    $planDetails = ['name' => 'Trial', 'allowed_level' => 1, 'has_seo_safe' => 0, 'has_anti_scraping' => 0];
}

$currentPlanName = $planDetails['name'];
$allowedLevel = (int)$planDetails['allowed_level'];
$hasSeoSafe = (bool)$planDetails['has_seo_safe'];
$hasAntiScraping = (bool)$planDetails['has_anti_scraping'];


if (!$config) {
    $apiKey = "API_Ainda_Nao_Configurada";
    $myLogs = 0;
    $myBlocks = 0;
    $acessos18 = 0;
    $totalAcessos = 0;
    $rate = "0.0";
} else {
    $apiKey = $config['api_key'] ?? "ag_" . bin2hex(random_bytes(16));
    $domainId = $config['id'];
    
    $totalAcessos = $pdo->query("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = " . (int)$userId . ")")->fetchColumn();
    $acessos18 = $pdo->query("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = " . (int)$userId . ") AND action NOT LIKE 'BLOCKED_%'")->fetchColumn();
    $myBlocks = $pdo->query("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = " . (int)$userId . ") AND action LIKE 'BLOCKED_%'")->fetchColumn();
    $rejeitados = $pdo->query("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = " . (int)$userId . ") AND action = 'REJECTED_CONSENT'")->fetchColumn();
    
    $rate = $totalAcessos > 0 ? number_format(($acessos18 / $totalAcessos) * 100, 1) : "0.0";
    $taxaRejeicao = $totalAcessos > 0 ? number_format(($rejeitados / $totalAcessos) * 100, 1) : "0.0";
    
    $stmtLogs = $pdo->prepare("SELECT * FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = ?) ORDER BY id DESC LIMIT 50");
    $stmtLogs->execute([$userId]);
    $recentLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

    $stmtOriginsList = $pdo->prepare("SELECT * FROM saas_origins WHERE user_id = ? ORDER BY id DESC");
    $stmtOriginsList->execute([$userId]);
    $myOrigins = $stmtOriginsList->fetchAll(PDO::FETCH_ASSOC);
}

$blocked = $myBlocks; // Alias
$recentLogs = $recentLogs ?? [];
$myOrigins = $myOrigins ?? [];

?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <link rel="icon" type="image/png" href="public/img/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Cliente | Front18 Pro</title>
    <!-- Mute Tailwind CDN Warning -->
    <script>
        const _ow = console.warn;
        console.warn = function(...args) {
            if (typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com')) return;
            _ow.apply(console, args);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: { 850: '#151f32', 900: '#0f172a', 950: '#020617' },
                        primary: { 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background-color: #020617; color: #f8fafc; overflow: hidden; }
        .glass-panel { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .sidebar-link { transition: all 0.2s; border-left: 2px solid transparent; }
        .sidebar-link.active { background: rgba(59, 130, 246, 0.1); border-left-color: #3b82f6; color: #60a5fa; }
        .sidebar-link:hover:not(.active) { background: rgba(255, 255, 255, 0.02); }
    </style>
</head>
<body class="flex h-screen bg-[#020617]">

    <!-- Sidebar SaaS -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col z-20 shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-slate-800 shrink-0">
            <img src="public/img/logo.png" alt="Front18 Logo" style="height: 18px; object-fit: contain;">
        </div>
        
        <div class="px-6 py-4 flex items-center gap-3 border-b border-slate-800">
            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                <i class="ph-fill ph-user text-slate-400 text-sm"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($demoName) ?></p>
                <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400 truncate">Plano <?= htmlspecialchars($currentPlanName) ?></p>
            </div>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1 custom-scrollbar">
            <!-- Core -->
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mb-2 mt-2">Diligência Legal</p>
            <button onclick="switchTab('home')" id="tab-btn-home" class="sidebar-link active w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-squares-four text-lg"></i> Visão Geral
            </button>
            <button onclick="switchTab('logs')" id="tab-btn-logs" class="sidebar-link w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <div class="flex items-center gap-3"><i class="ph-bold ph-list-dashes text-lg"></i> Logs Auditáveis</div>
                <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
            </button>
            <button onclick="switchTab('reports')" id="tab-btn-reports" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-file-pdf text-lg text-red-400"></i> Relatórios Dossiê
            </button>
            
            <!-- Controle -->
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mb-2 mt-6">Infraestrutura</p>
            <button onclick="switchTab('domains')" id="tab-btn-domains" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-globe text-lg"></i> Meus Domínios
            </button>
            <button onclick="switchTab('settings')" id="tab-btn-settings" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-sliders-horizontal text-lg"></i> Config. de Proteção
            </button>
            <button onclick="switchTab('appearance')" id="tab-btn-appearance" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-palette text-lg text-pink-400"></i> Personalização UI
            </button>
            <button onclick="switchTab('privacy')" id="tab-btn-privacy" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-cookie text-lg text-emerald-400"></i> Portal LGPD / DPO
            </button>
            <button onclick="switchTab('suspicious')" id="tab-btn-suspicious" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-warning-octagon text-lg text-orange-400"></i> Atividades Suspeitas
            </button>
            
            <!-- Account -->
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mb-2 mt-6">Conta</p>
            <button onclick="switchTab('billing')" id="tab-btn-billing" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-credit-card text-lg"></i> Assinatura
            </button>
            <button onclick="switchTab('api')" id="tab-btn-api" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-code text-lg text-indigo-400"></i> API e Integração
            </button>
            <button onclick="switchTab('wp')" id="tab-btn-wp" class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-slate-300 font-medium text-left">
                <i class="ph-bold ph-plugs-connected text-lg text-blue-400"></i> Integração WordPress
            </button>
        </nav>
        
        <div class="px-4 py-4 border-t border-slate-800">
            <a href="?route=logout" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm px-2">
                <i class="ph-bold ph-sign-out text-lg"></i> Sair da Plataforma
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <?php if(isset($_SESSION['superadmin_backup_id'])): ?>
        <div class="bg-indigo-600 text-white text-[11px] font-bold text-center py-2 flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 z-50">
            <i class="ph-bold ph-headset text-sm"></i> MODO ASSISTÊNCIA DE SUPORTE: Visualização delegada na conta do cliente B2B.
            <a href="?route=dashboard&cancel_impersonate=1" class="underline ml-2 hover:text-indigo-200 bg-black/20 px-3 py-1 rounded">Encerrar Sessão de Suporte</a>
        </div>
        <?php endif; ?>
        <?php if (!empty($user['is_trial']) && empty($user['plan_id'])): ?>
            <?php 
                $requestsLeft = max(0, 200 - $totalAcessos);
            ?>
            <?php if ($requestsLeft > 0): ?>
                <div class="bg-emerald-600 text-white text-[11px] font-bold text-center py-2 flex items-center justify-center gap-2 shadow-lg z-50">
                    <i class="ph-bold ph-gift text-sm"></i> TRIAL ATIVO: Rápido, você tem <?= $requestsLeft ?> requisições gratuitas restantes para provar a ferramenta.
                    <a href="#plans" onclick="switchTab('billing')" class="underline ml-2 hover:text-emerald-200 bg-black/20 px-3 py-1 rounded">Ativar Assinatura Definitiva</a>
                </div>
            <?php else: ?>
                <div class="bg-red-600 text-white text-[11px] font-bold text-center py-2 flex items-center justify-center gap-2 shadow-lg z-50">
                    <i class="ph-bold ph-warning-circle text-sm"></i> TRIAL GASTO: Sua franquia grátis acabou! O SDK Front18 sofrerá Bloqueio Fatal da nossa Infra em breve.
                    <a href="#plans" onclick="switchTab('billing')" class="underline ml-2 hover:text-red-200 bg-black/20 px-3 py-1 rounded">Regularizar Conta</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <header class="h-16 bg-[#020617]/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-8 z-10 shrink-0">
            <h2 id="page-title" class="font-bold text-lg text-white">Visão Geral</h2>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-2 text-xs font-mono text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-full border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Sistema Ativo
                </span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 relative">
            
            <!-- ====== TAB 1: HOME (Painel Central) ====== -->
            <div id="tab-home" class="tab-content active max-w-6xl mx-auto">
                
                <?php
                // Cálculo de UX do Progresso da Franquia
                $maxRequestsAllowed = (int)($planDetails['max_requests_per_month'] ?? 150000);
                $usagePercent = ($maxRequestsAllowed > 0) ? min(100, round(($totalAcessos / $maxRequestsAllowed) * 100, 1)) : 0;
                $usageColor = $usagePercent > 90 ? 'bg-red-500 shadow-red-500/50' : ($usagePercent > 75 ? 'bg-orange-500 shadow-orange-500/50' : 'bg-primary-500 shadow-primary-500/50');
                $textColor = $usagePercent > 90 ? 'text-red-400' : ($usagePercent > 75 ? 'text-orange-400' : 'text-primary-400');
                ?>
                <div class="glass-panel p-6 rounded-2xl mb-6 relative overflow-hidden group border border-slate-700/50">
                    <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-primary-900/10 to-transparent pointer-events-none"></div>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-4 gap-4 relative z-10">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i class="ph-bold ph-lightning text-primary-400"></i> Uso da Franquia SaaS Edge (Mensal)</p>
                            <h3 class="text-4xl font-black text-white tracking-tighter"><?= number_format($totalAcessos) ?> <span class="text-sm font-medium text-slate-500 tracking-normal hidden sm:inline-block">/ <?= number_format($maxRequestsAllowed) ?> requisições contratadas</span></h3>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span class="text-3xl font-black <?= $textColor ?>"><?= $usagePercent ?>%</span>
                            <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Consumido</span>
                        </div>
                    </div>
                    
                    <div class="w-full h-2.5 bg-[#0a0f18] rounded-full overflow-hidden border border-slate-800 shadow-inner relative z-10">
                        <div class="h-full <?= $usageColor ?> transition-all duration-1000 ease-out relative shadow-[0_0_10px_rgba(0,0,0,0.5)]" style="width: <?= $usagePercent ?>%">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent w-[200%] animate-[translateX_2s_infinite]"></div>
                        </div>
                    </div>
                    <style>@keyframes translateX { 0% { transform: translateX(-100%); } 100% { transform: translateX(50%); } }</style>
                    <div class="flex justify-between items-center mt-3 text-[10px] font-mono relative z-10">
                        <p class="text-slate-500 uppercase tracking-widest">Reseta no dia 01/<?= date('m', strtotime('+1 month', strtotime(date('Y-m-01')))) ?> à 00:00 UTC.</p>
                        <?= $usagePercent > 90 ? '<a href="#plans" class="text-red-400 font-bold hover:underline">Atenção ao Throttling! Faça Upgrade.</a>' : '<span class="text-emerald-500">Fluxo Saudável</span>' ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <!-- Cards Secundários -->
                    <div class="glass-panel p-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 relative overflow-hidden transition-transform hover:-translate-y-1">
                        <i class="ph-fill ph-check-circle absolute -right-4 -bottom-4 text-emerald-500/10 text-[100px] z-0 transition-transform group-hover:scale-110"></i>
                        <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1 relative z-10">Mídias Autorizadas</p>
                        <h3 class="text-3xl font-black text-emerald-300 relative z-10"><?= number_format($acessos18) ?></h3>
                        <p class="text-[10px] text-emerald-500 font-bold mt-1 relative z-10 px-2 py-0.5 bg-emerald-500/10 rounded w-fit"><?= $rate ?>% Conversão Legítima</p>
                    </div>
                    <div class="glass-panel p-5 rounded-2xl border border-red-500/20 bg-red-500/5 relative overflow-hidden transition-transform hover:-translate-y-1">
                        <i class="ph-fill ph-warning absolute -right-4 -bottom-4 text-red-500/10 text-[100px] z-0 transition-transform group-hover:rotate-12"></i>
                        <p class="text-xs font-bold text-red-400 uppercase tracking-widest mb-1 relative z-10">Abusos Bloqueados</p>
                        <h3 class="text-3xl font-black text-red-300 relative z-10"><?= number_format($blocked) ?></h3>
                        <p class="text-[10px] text-red-500 font-bold mt-1 relative z-10 px-2 py-0.5 bg-red-500/10 rounded w-fit">Menores / VPNs Barrados</p>
                    </div>
                    <div class="glass-panel p-5 rounded-2xl border border-amber-500/20 bg-gradient-to-br from-[#0b1120] to-slate-900 flex flex-col items-center justify-center text-center relative overflow-hidden transition-transform hover:-translate-y-1">
                        <i class="ph-bold ph-trend-down absolute left-4 top-4 text-amber-500/20 text-6xl"></i>
                        <p class="text-[10px] font-bold text-amber-400 uppercase tracking-widest leading-tight relative z-10 mb-1">Taxa de Rejeição de Verificação (Lead Dropout)</p>
                        <h3 class="text-3xl font-black text-amber-400 mt-1 relative z-10"><?= $taxaRejeicao ?>%</h3>
                        <p class="text-[9px] font-mono text-slate-400 mt-1 uppercase relative z-10 bg-slate-800/80 px-2 py-1 rounded truncate max-w-full"><?= number_format($rejeitados) ?> Usuários abandonaram o funil na tela raiz</p>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 glass-panel p-6 rounded-2xl">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-white text-lg">Evidências Recentes (Real-Time)</h3>
                            <button onclick="switchTab('logs')" class="text-xs text-primary-400 hover:text-primary-300 font-bold uppercase tracking-wider">Ver Todos &rarr;</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left font-mono text-xs">
                                <thead>
                                    <tr class="text-slate-500 border-b border-slate-800">
                                        <th class="pb-3 text-[10px] uppercase font-bold tracking-widest pl-2">Data/Hora</th>
                                        <th class="pb-3 text-[10px] uppercase font-bold tracking-widest">Status</th>
                                        <th class="pb-3 text-[10px] uppercase font-bold tracking-widest">IP (LGPD)</th>
                                        <th class="pb-3 text-[10px] uppercase font-bold tracking-widest text-right pr-2">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($recentLogs)): ?>
                                        <tr class="border-b border-slate-800/50"><td colspan="4" class="py-4 text-center text-slate-500">Nenhuma telemetria captada ainda.</td></tr>
                                    <?php else: ?>
                                        <?php $i=0; foreach($recentLogs as $log): if($i++>=5) break; 
                                            // Calcula o "Há X tempo" aproximado
                                            $td = time() - strtotime($log['created_at']);
                                            $timeStr = $td < 60 ? "Agora mesmo" : ($td < 3600 ? floor($td/60)." min" : floor($td/3600)."h");
                                        ?>
                                        <tr class="border-b border-slate-800/50 hover:bg-slate-800/30">
                                            <td class="py-3 text-slate-300 pl-2">Há <?= $timeStr ?></td>
                                            <td>
                                                <?php if(strpos($log['action'], 'BLOCKED') !== false): ?>
                                                    <span class="bg-red-500/10 text-red-400 px-2 py-0.5 rounded border border-red-500/20">Bloqueado</span>
                                                <?php else: ?>
                                                    <span class="bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/20">Autorizado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-slate-500"><?= htmlspecialchars($log['ip_address']) ?></td>
                                            <td class="text-right pr-2">
                                                <?php if(strpos($log['action'], 'BLOCKED') !== false): ?>
                                                    <i class="ph-bold ph-shield-warning text-red-500" title="Bloqueado: <?= htmlspecialchars($log['details']) ?>"></i>
                                                <?php else: ?>
                                                    <i class="ph-bold ph-file-dashed text-primary-400 hover:text-white" title="Hash: <?= htmlspecialchars($log['current_hash']) ?>"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="glass-panel p-6 rounded-2xl h-full flex flex-col relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900/10 to-transparent z-0"></div>
                        <h3 class="font-bold text-white text-lg mb-4 relative z-10 flex items-center gap-2"><i class="ph-fill ph-file-pdf text-red-400 text-xl"></i> Export Legal</h3>
                        <p class="text-xs text-slate-400 mb-6 leading-relaxed relative z-10">Gere e faça download de um dossiê imutável da cadeia de acessos deste mês para apresentação jurídica preventiva.</p>
                        <div class="mt-auto relative z-10">
                            <button onclick="switchTab('reports')" class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-bold text-xs py-3 rounded-lg flex justify-center items-center gap-2 transition-all shadow-md">
                                Acessar Gerador PDF <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== TAB 2: LOGS (Coração Jurídico) ====== -->
            <div id="tab-logs" class="tab-content max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-1">Cadeia de Custódia (Logs)</h2>
                        <p class="text-sm text-slate-400">Todo acesso é registrado de forma híbrida protegendo você e respeitando a LGPD.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="bg-slate-900 border border-slate-700 text-xs text-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                            <option>Últimos 7 dias</option>
                            <option>Últimos 30 dias</option>
                        </select>
                        <select class="bg-slate-900 border border-slate-700 text-xs text-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                            <option>Todos Domínios</option>
                            <?php foreach($myOrigins as $orig): ?>
                                <option><?= htmlspecialchars(str_replace(['http://','https://'],'', $orig['domain'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="switchTab('reports')" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 border border-red-500 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all shadow-lg shadow-red-500/20 flex items-center gap-2">
                            <i class="ph-bold ph-file-pdf"></i> Dossiê Evidência PDF
                        </button>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl overflow-hidden border border-slate-700/50">
                    <table class="w-full text-left font-mono text-[11px]">
                        <thead class="bg-slate-900/80 border-b border-slate-800">
                            <tr class="text-slate-400">
                                <th class="px-6 py-4 uppercase font-bold tracking-widest">Data / Hora (UTC)</th>
                                <th class="px-6 py-4 uppercase font-bold tracking-widest">Client IP</th>
                                <th class="px-6 py-4 uppercase font-bold tracking-widest">Status / Motivo</th>
                                <th class="px-6 py-4 uppercase font-bold tracking-widest">Hash de Registro (Assinatura)</th>
                                <th class="px-6 py-4 uppercase font-bold tracking-widest">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-300">
                            <?php if(empty($recentLogs)): ?>
                                <tr class="border-b border-slate-800/50"><td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">O seu Dossiê Forense está vazio. Aguardando acessos no seu domínio via SDK.</td></tr>
                            <?php else: ?>
                                <?php foreach($recentLogs as $log): ?>
                                <tr class="border-b border-slate-800/50 hover:bg-slate-800/30">
                                    <td class="px-6 py-4 truncate max-w-[150px]"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td class="px-6 py-4 text-slate-500 flex items-center gap-2 truncate max-w-[150px]">
                                        <i class="ph-fill ph-globe-hemisphere-west text-slate-600"></i> <?= htmlspecialchars($log['ip_address']) ?>
                                    </td>
                                    <td class="px-6 py-4 truncate max-w-[150px]">
                                        <?php if(strpos($log['action'], 'BLOCKED') !== false): ?>
                                            <span class="bg-red-500/10 text-red-400 px-2 py-0.5 rounded border border-red-500/20 whitespace-nowrap">Bloqueado na Borda</span>
                                        <?php else: ?>
                                            <span class="bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/20 whitespace-nowrap">Autorizado (Diligência)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 truncate max-w-[150px]" title="<?= htmlspecialchars($log['current_hash'] ?? 'N/A') ?>">
                                        <?= $log['current_hash'] ? 'SHA256: ' . substr($log['current_hash'], 0, 16) . '...' : '-' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="/public/api/report_single.php?log_id=<?= $log['id'] ?>" target="_blank" class="text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-bold text-[10px] uppercase tracking-wider bg-indigo-500/10 border border-indigo-500/20 px-2 py-1 rounded w-fit transition-colors">
                                            <i class="ph-bold ph-certificate text-sm"></i> Laudo
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="px-6 py-4 border-t border-slate-800 text-center text-[10px] text-slate-500 flex justify-between items-center">
                        <button onclick="FrontToast.show('warning', 'Paginação granular de Big Data é restrita ao seu plano atual. Acesse a aba de Dossiês PDF para relatórios mensais completos.')" class="px-3 py-1 bg-slate-800 rounded hover:text-white transition-colors uppercase font-bold tracking-wider">Histórico Mais Antigo</button>
                        <span>Exibindo recortes recentes de telemetria.</span>
                        <button onclick="FrontToast.show('warning', 'Você já está vendo as entradas mais recentes da cadeia em tempo real.')" class="px-3 py-1 bg-slate-800 rounded hover:text-white transition-colors uppercase font-bold tracking-wider opacity-50 cursor-not-allowed">Nova Página</button>
                    </div>
                </div>
            </div>

            <!-- ====== TAB 3: REPORTS (Arma Jurídica) ====== -->
            <div id="tab-reports" class="tab-content max-w-4xl mx-auto">
                <div class="text-center mb-10">
                    <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/20 text-red-500 flex items-center justify-center text-3xl mx-auto mb-4"><i class="ph-fill ph-file-pdf"></i></div>
                    <h2 class="text-3xl font-bold text-white mb-2">Central de Laudos em PDF</h2>
                    <p class="text-slate-400 text-sm max-w-lg mx-auto">Em caso de notificação extrajudicial, gere o dossiê com a cadeia de custódia completa do período para comprovar blindagem passiva do seu veículo.</p>
                </div>

                <div class="glass-panel p-8 rounded-3xl">
                    <form action="/public/api/report.php" method="GET" target="_blank" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Domínio Operacional</label>
                                <select name="domain_id" required class="w-full bg-slate-900 border border-slate-700 text-sm text-white rounded-xl px-4 py-3 focus:outline-none focus:border-primary-500">
                                    <?php foreach($myOrigins as $orig): ?>
                                        <option value="<?= $orig['id'] ?>"><?= htmlspecialchars(str_replace(['http://','https://'],'', $orig['domain'])) ?> (Protegido)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Mês do Relatório Legal</label>
                                <select name="period" required class="w-full bg-slate-900 border border-slate-700 text-sm text-white rounded-xl px-4 py-3 focus:outline-none focus:border-primary-500">
                                    <option value="<?= date('Y-m') ?>">Mês Atual (<?= date('m/Y') ?>)</option>
                                    <option value="<?= date('Y-m', strtotime('-1 month')) ?>">Mês Passado (<?= date('m/Y', strtotime('-1 month')) ?>)</option>
                                    <option value="all">Todo o Histórico Vitalício</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl space-y-3">
                            <h4 class="text-xs font-bold text-white mb-4 uppercase tracking-wider">O que constará neste documento:</h4>
                            <p class="text-xs text-slate-400 flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Resumo técnico e Score de Compliance do Domínio.</p>
                            <p class="text-xs text-slate-400 flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Amostragem em tabela tabular dos últimos acessos mascarados (LGPD).</p>
                            <p class="text-xs text-slate-400 flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Declaração assinada digitalmente de Diligência de Boa-Fé (SaaS Escudo Civil).</p>
                            <p class="text-xs text-slate-400 flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Códigos Hash da Cadeia de Integridade Criptográfica.</p>
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-red-500/20 transition-all">
                            Emitir Documento (PDF Oficial)
                        </button>
                    </form>
                </div>
            </div>

            <!-- ====== TAB 4: DOMAINS (Escala) ====== -->
            <div id="tab-domains" class="tab-content max-w-5xl mx-auto">
                <div class="mb-10 text-center max-w-3xl mx-auto">
                    <div class="w-16 h-16 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-500 flex items-center justify-center text-3xl mx-auto mb-4"><i class="ph-fill ph-globe-hemisphere-east"></i></div>
                    <h2 class="text-3xl font-bold text-white mb-3">Conexão Zero-Config (Plug & Play)</h2>
                    <p class="text-slate-400 text-sm leading-relaxed">Na Arquitetura SaaS do Front18 você não precisa ficar desenhando infraestrutura nativa nem conectando IPs. Basta <strong>Adicionar a URL do seu Site</strong> abaixo para que nosso cérebro gere uma <strong class="text-primary-400">Chave de API única (Token Criptográfico)</strong>. Use essa chave lá no seu site (via Plugin WordPress ou HTML) e seu Domínio estará super protegido e sincronizado conosco!</p>
                </div>
                
                <?php if(isset($_SESSION['dashboard_error'])): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-500 px-4 py-3 rounded-xl mb-6 flex items-center justify-center gap-2 max-w-3xl mx-auto shadow-lg shadow-red-500/10">
                        <i class="ph-bold ph-warning text-lg"></i> <span><?= htmlspecialchars($_SESSION['dashboard_error']) ?></span>
                    </div>
                <?php unset($_SESSION['dashboard_error']); endif; ?>

                <?php if(isset($_SESSION['dashboard_success'])): ?>
                    <div class="bg-primary-500/10 border border-primary-500/30 text-primary-400 px-4 py-3 rounded-xl mb-6 flex items-center justify-center gap-2 max-w-3xl mx-auto shadow-lg shadow-primary-500/10">
                        <i class="ph-bold ph-check-circle text-lg"></i> <span><?= htmlspecialchars($_SESSION['dashboard_success']) ?></span>
                    </div>
                <?php unset($_SESSION['dashboard_success']); endif; ?>
                
                <!-- Didática + Formulário Mestre -->
                <form method="POST" class="glass-panel p-8 rounded-3xl mb-12 border border-primary-500/20 relative overflow-hidden" onsubmit="this.querySelector('button').innerHTML='Provisionando...';">
                    <input type="hidden" name="action" value="add_domain">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-600/10 blur-[80px] rounded-full pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 items-center">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2 flex items-center gap-1"><i class="ph-fill ph-link"></i> URL Base do seu Site (Ex: meulojão.com.br)</label>
                            <input type="text" name="domain_url" required placeholder="www.meusite.com.br" class="w-full bg-slate-900 border border-slate-700 text-white rounded-xl px-5 py-4 focus:outline-none focus:border-primary-500 transition-colors shadow-inner" style="font-family: 'JetBrains Mono', monospace; font-size:14px;">
                        </div>
                        <div class="w-full md:w-auto mt-2 md:mt-0 pt-4 md:pt-4">
                            <button type="submit" class="w-full h-full bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-bold py-4 px-10 rounded-xl transition-all shadow-lg shadow-primary-500/25 whitespace-nowrap flex items-center justify-center gap-2">
                                <i class="ph-bold ph-key"></i> Gerar API Key Exclusiva
                            </button>
                        </div>
                    </div>
                </form>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-white">Seus Domínios e Chaves</h2>
                    <span class="text-xs text-slate-500 bg-slate-900 px-3 py-1 rounded border border-slate-800">Franquia Contratada: <?= count($myOrigins) ?> / <?= $planDetails['max_domains'] ?? 1 ?> Sites</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if(empty($myOrigins)): ?>
                        <div class="glass-panel p-6 rounded-2xl col-span-2 text-center text-slate-400 py-16 border-dashed border-2 border-slate-700/50">
                            <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center text-slate-600 text-4xl mx-auto mb-4"><i class="ph-fill ph-ghost"></i></div>
                            <p class="text-lg font-bold text-white mb-2">Nenhum Token de API Ativo no Seu Perfil</p>
                            <p class="text-sm">Cadastre a URL do seu primeiro site na caixa acima para começarmos e receber sua chave criptográfica.</p>
                        </div>
                    <?php else: foreach($myOrigins as $orig): ?>
                    <div class="glass-panel p-6 rounded-2xl relative">
                        <div class="absolute top-6 right-6 flex gap-2">
                            <span class="<?= $orig['is_active'] ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' ?> px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                <?= $orig['is_active'] ? 'Online & Protegido' : 'Suspenso (WAF)' ?>
                            </span>
                        </div>
                        <h4 class="text-white font-bold text-lg mb-1 flex items-center gap-2"><i class="ph-fill ph-globe text-primary-400"></i> <?= htmlspecialchars(str_replace(['http://', 'https://'], '', $orig['domain'])) ?></h4>
                        <p class="text-[10px] text-slate-500 font-mono mb-4 uppercase tracking-wider">Token de Autoridade Criptográfica (API KEY):</p>
                        
                        <div class="bg-slate-900 border border-slate-700/50 rounded-lg px-4 py-2 flex items-center justify-between mb-4 group cursor-pointer hover:border-primary-500/50" onclick="navigator.clipboard.writeText('<?= $orig['api_key'] ?>'); FrontToast.show('success', 'Key Secreta copiada para a área de transferência!');">
                            <code class="text-xs text-amber-400 font-mono truncate max-w-[200px]"><?= htmlspecialchars($orig['api_key']) ?></code>
                            <button class="text-slate-400 group-hover:text-primary-400 transition-colors"><i class="ph-bold ph-copy"></i></button>
                        </div>
                        
                        <div class="flex flex-wrap items-center justify-between gap-4 text-sm mt-4 border-t border-slate-800 pt-4">
                            <div class="flex items-center gap-4">
                                <button onclick="switchTab('settings')" class="text-slate-400 hover:text-white font-bold flex items-center gap-1"><i class="ph-bold ph-gear"></i> Setup Legal</button>
                                <button onclick="switchTab('api')" class="text-indigo-400 hover:text-white font-bold flex items-center gap-1"><i class="ph-bold ph-code"></i> Implantação</button>
                            </div>
                            <form method="POST" onsubmit="return confirm('ATENÇÃO: Deletar a propriedade revoga imediatamente essa API Key e exclui todas as provas forenses ligadas a ela do banco. Deseja continuar?');">
                                <input type="hidden" name="action" value="delete_domain">
                                <input type="hidden" name="domain_id" value="<?= $orig['id'] ?>">
                                <button type="submit" class="text-red-500 hover:text-red-400 font-bold flex items-center gap-1 bg-red-500/10 hover:bg-red-500/20 px-3 py-1 rounded transition-colors"><i class="ph-bold ph-trash"></i> Excluir</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- ====== TAB 5: SETTINGS (Controle) ====== -->
            <div id="tab-settings" class="tab-content max-w-4xl mx-auto">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-white/5">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-sliders-horizontal text-xl text-primary-400"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white leading-tight">Painel de Blindagem</h2>
                        <p class="text-sm text-slate-400">Configurações Globais WAF: <strong class="text-white"><?= empty($myOrigins) ? 'Nenhum Domínio Cadastrado' : htmlspecialchars(str_replace(['http://', 'https://'], '', $myOrigins[0]['domain'])) ?></strong></p>
                    </div>
                </div>

                <form id="frmSettings" class="space-y-6" onsubmit="event.preventDefault(); syncEdgeConfig(this);">
                    <div class="glass-panel p-6 rounded-2xl border-l-[4px] border-l-primary-500 mb-8">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-white text-lg">Resumo das Suas Permissões (Plano <?= htmlspecialchars($currentPlanName) ?>)</h3>
                                <p class="text-xs text-slate-400 mt-1">O seu pacote dita o poder bélico do WAF. Aqui está o escopo de atuação do seu contrato:</p>
                            </div>
                            <a href="#plans" onclick="switchTab('billing')" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors shadow-sm">
                                Fazer Upgrade
                            </a>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl text-center">
                                <i class="ph-bold ph-lightning text-slate-500 text-xl mb-2"></i>
                                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Cota de Requisições</p>
                                <p class="font-mono text-emerald-400 font-bold"><?= number_format($maxRequestsAllowed) ?> <span class="text-[9px] text-slate-500">/mês</span></p>
                            </div>
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl text-center">
                                <i class="ph-bold ph-shield text-slate-500 text-xl mb-2"></i>
                                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Força da Catraca</p>
                                <p class="font-bold text-white">Nível <?= $allowedLevel ?> (Max)</p>
                            </div>
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl text-center">
                                <i class="ph-bold ph-magnifying-glass text-slate-500 text-xl mb-2"></i>
                                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">SEO Orgânico</p>
                                <?= $hasSeoSafe ? '<span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs rounded font-bold">Incluso</span>' : '<span class="px-2 py-0.5 bg-slate-800 text-slate-500 text-xs rounded font-bold border border-slate-700">Bloqueado</span>' ?>
                            </div>
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl text-center">
                                <i class="ph-bold ph-wall text-slate-500 text-xl mb-2"></i>
                                <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Anti-Scraping</p>
                                <?= $hasAntiScraping ? '<span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs rounded font-bold">Incluso</span>' : '<span class="px-2 py-0.5 bg-slate-800 text-slate-500 text-xs rounded font-bold border border-slate-700">Bloqueado</span>' ?>
                            </div>
                        </div>
                    </div>

                    <!-- MODO DE EXIBIÇÃO: GLOBAL VS MEDIA BLUR -->
                    <div class="glass-panel p-6 rounded-2xl">
                        <h4 class="font-black text-white text-lg mb-1 flex items-center gap-2"><span class="bg-primary-500 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">1</span> Estratégia do Funil Visível</h4>
                        <p class="text-[11px] text-slate-400 mb-6 pb-4 border-b border-slate-800">Defina se todo o site bloqueia de cara, ou se você criará um "Teaser" prendendo fotos e textos curiosos.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="block cursor-pointer relative group">
                                <input type="radio" name="display_mode" value="global_lock" class="peer sr-only" <?= ($config['display_mode'] ?? 'global_lock') === 'global_lock' ? 'checked' : '' ?>>
                                <div class="bg-slate-950 border border-slate-800 p-5 rounded-2xl transition-all peer-checked:border-primary-500 peer-checked:bg-primary-900/10 hover:border-slate-600 flex flex-col h-full peer-checked:[&_.check-bubble]:bg-primary-500 peer-checked:[&_.check-bubble]:border-primary-500 peer-checked:[&_.check-icon]:opacity-100">
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center shrink-0 shadow-inner group-hover:text-primary-400 transition-colors"><i class="ph-bold ph-lock-key text-xl"></i></div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-white text-base">Catraca Global (Front Door)</h5>
                                            <div class="<?= ($config['display_mode'] ?? 'global_lock') === 'global_lock' ? 'text-primary-400 font-bold text-[10px] uppercase tracking-wider mt-1' : 'hidden' ?>">Modo Atual Ativo</div>
                                        </div>
                                        <div class="check-bubble w-6 h-6 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all bg-slate-900">
                                            <i class="check-icon ph-bold ph-check text-white opacity-0 transition-opacity text-xs"></i>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-relaxed mt-1 flex-1">A tela do site sequer é exibida. O bloqueio desce como uma cortina preta no segundo zero. Indicado para portais severos e marcas rígidas onde ler o texto já é proibido.</p>
                                </div>
                            </label>

                            <label class="block cursor-pointer relative group">
                                <input type="radio" name="display_mode" value="blur_media" class="peer sr-only" <?= ($config['display_mode'] ?? 'global_lock') === 'blur_media' ? 'checked' : '' ?>>
                                <div class="bg-slate-950 border border-slate-800 p-5 rounded-2xl transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-900/10 hover:border-slate-600 flex flex-col h-full peer-checked:[&_.check-bubble]:bg-emerald-500 peer-checked:[&_.check-bubble]:border-emerald-500 peer-checked:[&_.check-icon]:opacity-100">
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 flex items-center justify-center shrink-0 shadow-inner group-hover:text-emerald-400 transition-colors"><i class="ph-bold ph-image text-xl"></i></div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-white text-base">Media Teaser (Recomendado)</h5>
                                            <div class="<?= ($config['display_mode'] ?? 'global_lock') === 'blur_media' ? 'text-emerald-400 font-bold text-[10px] uppercase tracking-wider mt-1' : 'hidden' ?>">Modo Atual Ativo</div>
                                        </div>
                                        <div class="check-bubble w-6 h-6 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all bg-slate-900">
                                            <i class="check-icon ph-bold ph-check text-white opacity-0 transition-opacity text-xs"></i>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-relaxed mt-1 flex-1">O site carrega limpo para o visitante ler (aumenta o tráfego 10x). Somente fotos e vídeos ficam borrados. Ao demonstrar interesse clicando neles, a catraca levanta para converter o lead.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- BLUR SETTINGS (Apenas se Media Teaser ou Custom) -->
                    <div class="glass-panel p-6 rounded-2xl">
                        <h4 class="font-black text-white text-lg mb-1 flex items-center gap-2"><span class="bg-primary-500 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-xs"><i class="ph-bold ph-sliders"></i></span> Personalização do Media Teaser</h4>
                        <p class="text-[11px] text-slate-400 mb-6 pb-4 border-b border-slate-800">Defina o nível de censura prévia e os elementos/plugins que serão interceptados antes do cliente validar a sessão.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Coluna Esquerda: Preview e Range -->
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-3">Intensidade do Desfoque</label>
                                
                                <div class="relative w-full h-40 rounded-xl overflow-hidden border border-slate-700 mb-4 bg-slate-900 flex items-center justify-center group pointer-events-none">
                                    <img src="https://images.unsplash.com/photo-1542282088-fe8426682b8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Preview Blur" class="absolute inset-0 w-full h-full object-cover transition-all" id="blur_preview_img" style="filter: blur(<?= htmlspecialchars($config['blur_amount'] ?? 25) ?>px) grayscale(50%) saturate(1.5);">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <i class="ph-bold ph-eye text-white/50 text-2xl mb-1"></i>
                                        <span class="text-white text-[10px] font-bold tracking-widest uppercase shadow-black drop-shadow-md">Preview Teaser</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <input type="range" min="5" max="50" value="<?= htmlspecialchars($config['blur_amount'] ?? 25) ?>" 
                                        class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                                        oninput="document.getElementById('blur_amount_display_input').value = this.value; document.getElementById('blur_amount_display').innerText = this.value + 'px'; document.getElementById('blur_preview_img').style.filter = 'blur(' + this.value + 'px) grayscale(50%) saturate(1.5)';">
                                    <!-- Hidden Input required for the Backend Save -->
                                    <input type="hidden" name="blur_amount" id="blur_amount_display_input" value="<?= htmlspecialchars($config['blur_amount'] ?? 25) ?>">
                                    <div class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 font-mono text-emerald-400 font-bold shrink-0" id="blur_amount_display">
                                        <?= htmlspecialchars($config['blur_amount'] ?? 25) ?>px
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-2">Dica: 20-30px costuma ser ideal para ocultar intimidade (+18) retendo a curiosidade visual.</p>
                            </div>

                            <!-- Coluna Direita: Seletores -->
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-widest mb-3">Mídia Alvo (Interceptação)</label>
                                <?php 
                                    $current_selector = !empty($config['blur_selector']) ? $config['blur_selector'] : 'img, video, iframe, [data-front18="locked"]'; 
                                    $mode = 'custom';
                                    if ($current_selector === 'img, video, iframe, [data-front18="locked"]') $mode = 'default';
                                    $elementor_selector = 'img, video, iframe, [data-front18="locked"], .elementor-loop-container article, .elementor-widget-loop-builder .elementor-post';
                                    if ($current_selector === $elementor_selector) $mode = 'elementor';
                                ?>
                                
                                <div class="space-y-3 mb-4">
                                    <!-- Option 1 -->
                                    <label class="flex p-3 border rounded-xl cursor-pointer transition-all <?= $mode === 'default' ? 'bg-emerald-900/10 border-emerald-500' : 'bg-slate-900 border-slate-700 hover:border-slate-500' ?>" onclick="selectBlurPreset('default', this)">
                                        <input type="radio" name="_blur_preset" value="default" class="sr-only" <?= $mode === 'default' ? 'checked' : '' ?>>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h5 class="preset-title text-sm font-bold <?= $mode === 'default' ? 'text-emerald-400' : 'text-slate-300' ?> flex items-center gap-2"><i class="ph-bold ph-image-square"></i> Nativo (Recomendado)</h5>
                                                <i class="preset-icon ph-fill ph-check-circle text-emerald-500 <?= $mode === 'default' ? 'opacity-100' : 'opacity-0' ?>"></i>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Intercepta &lt;img&gt; padrão, &lt;video&gt; do WordPress e iframe (YouTube)</p>
                                        </div>
                                    </label>
                                    
                                    <!-- Option 2 -->
                                    <label class="flex p-3 border rounded-xl cursor-pointer transition-all <?= $mode === 'elementor' ? 'bg-emerald-900/10 border-emerald-500' : 'bg-slate-900 border-slate-700 hover:border-slate-500' ?>" onclick="selectBlurPreset('elementor', this)">
                                        <input type="radio" name="_blur_preset" value="elementor" class="sr-only" <?= $mode === 'elementor' ? 'checked' : '' ?>>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h5 class="preset-title text-sm font-bold <?= $mode === 'elementor' ? 'text-emerald-400' : 'text-slate-300' ?> flex items-center gap-2"><i class="ph-bold ph-squares-four"></i> Especial Elementor Pro</h5>
                                                <i class="preset-icon ph-fill ph-check-circle text-emerald-500 <?= $mode === 'elementor' ? 'opacity-100' : 'opacity-0' ?>"></i>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Blinda nativos + Injeções do Elementor Loop Grid & Post Carousels</p>
                                        </div>
                                    </label>
                                    
                                    <!-- Option 3 -->
                                    <label class="flex p-3 border rounded-xl cursor-pointer transition-all <?= $mode === 'custom' ? 'bg-amber-900/10 border-amber-500' : 'bg-slate-900 border-slate-700 hover:border-slate-500' ?>" onclick="selectBlurPreset('custom', this)">
                                        <input type="radio" name="_blur_preset" value="custom" class="sr-only" <?= $mode === 'custom' ? 'checked' : '' ?>>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h5 class="preset-title text-sm font-bold <?= $mode === 'custom' ? 'text-amber-400' : 'text-slate-300' ?> flex items-center gap-2"><i class="ph-bold ph-code"></i> Desenvolvedor</h5>
                                                <i class="preset-icon ph-fill ph-check-circle text-amber-500 <?= $mode === 'custom' ? 'opacity-100' : 'opacity-0' ?>"></i>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1 leading-tight">Abre console para digitar Classes CSS ou Elementos personalizados.</p>
                                        </div>
                                    </label>
                                </div>

                                <div id="custom_blur_area" class="<?= $mode !== 'custom' ? 'hidden' : '' ?> animate-[fadeIn_0.3s_ease]">
                                    <label class="block text-[10px] text-amber-500/80 mb-2 font-bold uppercase tracking-widest"><i class="ph-bold ph-terminal-window"></i> Query Selector Alvo:</label>
                                    <textarea name="blur_selector" id="blur_selector_input" rows="3" class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl p-4 focus:outline-none focus:border-amber-500/50 font-mono text-xs custom-scrollbar leading-relaxed resize-none shadow-inner"><?= htmlspecialchars($current_selector) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel p-6 rounded-2xl">
                        <h4 class="font-black text-white text-lg mb-1 flex items-center gap-2"><span class="bg-primary-500 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">2</span> Força da Catraca Jurídica</h4>
                        <p class="text-[11px] text-slate-400 mb-6 pb-4 border-b border-slate-800">Uma vez que a barreira é ativada (seja global ou clicando no Teaser), qual será o design e o isolamento processual do Front18?</p>
                        
                        <div class="space-y-4">
                            <!-- Nível 1: Blur -->
                            <label class="block cursor-pointer relative group">
                                <input type="radio" name="level" value="1" class="peer sr-only" <?= ($config['protection_level'] ?? 2) == 1 ? 'checked' : '' ?>>
                                <div class="bg-slate-950 border border-slate-800 p-5 rounded-2xl transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-900/10 hover:border-slate-700 flex flex-col md:flex-row gap-6 items-center peer-checked:[&_.check-bubble]:bg-indigo-500 peer-checked:[&_.check-bubble]:border-indigo-500 peer-checked:[&_.check-icon]:opacity-100">
                                    <div class="w-full md:w-32 h-20 bg-slate-900 rounded-lg relative overflow-hidden shrink-0 border border-slate-800 shadow-inner flex items-center justify-center">
                                        <div class="absolute inset-0 opacity-20 filter blur-[2px] bg-[url('https://images.unsplash.com/photo-1542282088-fe8426682b8f')] bg-cover bg-center"></div>
                                        <div class="absolute inset-0 bg-slate-900/60 font-mono text-[8px] flex items-center justify-center text-indigo-300 backdrop-blur-sm">MODAL</div>
                                    </div>
                                    <div class="flex-1 w-full">
                                        <div class="flex justify-between items-start mb-1">
                                            <h5 class="font-bold text-white text-md">Level 1: Modal em Blur <span class="bg-slate-800 text-slate-400 text-[9px] px-2 py-0.5 rounded ml-2">Básico</span></h5>
                                            <div class="check-bubble w-6 h-6 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all bg-slate-900 shrink-0">
                                                <i class="check-icon ph-bold ph-check text-white opacity-0 transition-opacity text-xs"></i>
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed mb-2 max-w-2xl">O fundo da tela recebe um desfoque fosco mantendo a identidade visual do site no fundo. O código da página já carrega por baixo do modal.</p>
                                    </div>
                                </div>
                            </label>

                            <!-- Nível 2: Blackout -->
                            <label class="block <?= ($allowedLevel < 2) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group' ?> relative">
                                <?php if($allowedLevel < 2): ?>
                                    <div class="absolute inset-0 z-20 bg-slate-950/70 rounded-2xl flex items-center justify-center backdrop-blur-[1px]">
                                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-3 py-1.5 rounded flex items-center gap-1 shadow-lg"><i class="ph-bold ph-lock-key"></i> Seu Plano atinge apenas o Nível 1 - Faça Upgrade</span>
                                    </div>
                                <?php endif; ?>
                                <input type="radio" name="level" value="2" class="peer sr-only" <?= ($config['protection_level'] ?? 1) == 2 ? 'checked' : '' ?> <?= ($allowedLevel < 2) ? 'disabled' : '' ?>>
                                <div class="bg-slate-950 border border-slate-800 p-5 rounded-2xl transition-all peer-checked:border-orange-500 peer-checked:bg-orange-900/10 hover:border-slate-700 flex flex-col md:flex-row gap-6 items-center peer-checked:[&_.check-bubble]:bg-orange-500 peer-checked:[&_.check-bubble]:border-orange-500 peer-checked:[&_.check-icon]:opacity-100 overflow-hidden relative">
                                    <div class="w-full md:w-32 h-20 bg-[#020617] rounded-lg relative overflow-hidden shrink-0 border border-slate-800 shadow-inner flex items-center justify-center">
                                        <i class="ph-fill ph-lock-key text-orange-500 opacity-50 text-3xl"></i>
                                    </div>
                                    <div class="flex-1 w-full">
                                        <div class="flex justify-between items-start mb-1">
                                            <h5 class="font-bold text-white text-md">Level 2: Fundo Negro Isolado <span class="bg-orange-500/20 text-orange-400 text-[9px] px-2 py-0.5 rounded ml-2 border border-orange-500/30 uppercase tracking-widest font-black">Profissional</span></h5>
                                            <div class="check-bubble w-6 h-6 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all bg-slate-900 shrink-0 <?= ($config['protection_level'] ?? 1) == 2 ? 'bg-orange-500 border-orange-500' : '' ?>">
                                                <i class="check-icon ph-bold ph-check text-white <?= ($config['protection_level'] ?? 1) == 2 ? 'opacity-100' : 'opacity-0' ?> transition-opacity text-xs"></i>
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed mb-2 max-w-2xl">Remove distrações. A janela do navegador fica 100% preta com máxima atenção ao contrato jurídico. HTML bloqueado agressivamente.</p>
                                    </div>
                                </div>
                            </label>

                            <!-- Nível 3: Paranoia -->
                            <label class="block <?= ($allowedLevel < 3) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group' ?> relative">
                                <?php if($allowedLevel < 3): ?>
                                    <div class="absolute inset-0 z-20 bg-slate-950/70 rounded-2xl flex items-center justify-center backdrop-blur-[1px]">
                                        <span class="bg-red-600 text-white text-[10px] font-bold px-3 py-1.5 rounded flex items-center gap-1 shadow-lg"><i class="ph-bold ph-lock-key"></i> Extremo - Requer Plano Avançado</span>
                                    </div>
                                <?php endif; ?>
                                <input type="radio" name="level" value="3" class="peer sr-only" <?= ($config['protection_level'] ?? 1) == 3 ? 'checked' : '' ?> <?= ($allowedLevel < 3) ? 'disabled' : '' ?>>
                                <div class="bg-slate-950 border border-slate-800 p-5 rounded-2xl transition-all peer-checked:border-red-500 peer-checked:bg-red-900/10 hover:border-slate-700 flex flex-col md:flex-row gap-6 items-center peer-checked:[&_.check-bubble]:bg-red-500 peer-checked:[&_.check-bubble]:border-red-500 peer-checked:[&_.check-icon]:opacity-100 relative">
                                    <div class="w-full md:w-32 h-20 bg-slate-950 rounded-lg relative overflow-hidden shrink-0 border border-red-900/50 shadow-inner flex items-center justify-center">
                                        <div class="absolute inset-0 bg-red-900/20 mix-blend-color-burn"></div>
                                        <i class="ph-bold ph-fingerprint text-red-500 text-3xl opacity-50"></i>
                                    </div>
                                    <div class="flex-1 w-full">
                                         <div class="flex justify-between items-start mb-1">
                                            <h5 class="font-bold text-white text-md">Level 3: Zero-Trust WAF <span class="bg-red-500 text-white text-[9px] px-2 py-0.5 rounded ml-2 border border-red-600 uppercase tracking-widest font-black">Paranóico</span></h5>
                                            <div class="check-bubble w-6 h-6 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all bg-slate-900 shrink-0 <?= ($config['protection_level'] ?? 1) == 3 ? 'bg-red-500 border-red-500' : '' ?>">
                                                <i class="check-icon ph-bold ph-check text-white <?= ($config['protection_level'] ?? 1) == 3 ? 'opacity-100' : 'opacity-0' ?> transition-opacity text-xs"></i>
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed max-w-2xl">Ativa todos os gatilhos severos e criptografia XOR. Recomendado apenas se o site for alvo constante de fiscalização rigorosa.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Módulos Avançados -->
                    <div class="glass-panel p-6 rounded-2xl">
                        <h4 class="font-black text-white text-lg mb-1 flex items-center gap-2"><span class="bg-primary-500 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">3</span> Módulos de Defesa Extra</h4>
                        <p class="text-[11px] text-slate-400 mb-6 pb-4 border-b border-slate-800">Recursos extras de mitigação baseados no plano. Defesas inativas devido a restrições de contrato não podem ser ativadas.</p>
                        
                        <div class="space-y-4">
                            <!-- Anti Scraping -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 transition-colors hover:border-slate-700 relative">
                                <?php if(!$hasAntiScraping): ?>
                                    <div class="absolute inset-0 z-20 bg-slate-950/70 rounded-xl flex items-center justify-center backdrop-blur-[1px]">
                                        <span class="bg-indigo-600/90 border border-indigo-400/50 text-white text-[10px] font-bold px-3 py-1.5 rounded flex items-center gap-1 shadow-lg"><i class="ph-bold ph-lock-key"></i> Upgrade Requerido</span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="font-bold text-sm text-slate-200 flex items-center gap-2"><i class="ph-bold ph-wall text-red-500"></i> WAF Anti-Scraping & VPNs</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-lg">Botnet Mitigation. Barreira ativa contra raspadores de imagens, tráfegos de data centers ocultos e redes Tor (Anonimização).</p>
                                </div>
                                <label class="relative inline-flex items-center <?= !$hasAntiScraping ? 'opacity-50' : 'cursor-pointer' ?>">
                                  <input type="checkbox" name="anti_scraping" value="1" class="sr-only peer" <?= (isset($config['anti_scraping']) ? $config['anti_scraping'] : 1) ? 'checked' : '' ?> <?= !$hasAntiScraping ? 'disabled' : '' ?>>
                                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 transition-colors shadow-inner"></div>
                                </label>
                            </div>
                            
                            <!-- SEO Safe -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 transition-colors hover:border-slate-700 relative">
                                <?php if(!$hasSeoSafe): ?>
                                    <div class="absolute inset-0 z-20 bg-slate-950/70 rounded-xl flex items-center justify-center backdrop-blur-[1px]">
                                        <span class="bg-indigo-600/90 border border-indigo-400/50 text-white text-[10px] font-bold px-3 py-1.5 rounded flex items-center gap-1 shadow-lg"><i class="ph-bold ph-lock-key"></i> Extensão Não Coberta pelo Contrato</span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="font-bold text-sm text-slate-200 flex items-center gap-2"><i class="ph-bold ph-magnifying-glass text-blue-400"></i> SEO Safe Core (Googlebot Pass)</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-lg">Permite que motores de busca leiam o texto mascarado do site para fins de Indexação Positiva, sem que o cliente perca pontuação de SEO pelo bloqueio.</p>
                                </div>
                                <label class="relative inline-flex items-center <?= !$hasSeoSafe ? 'opacity-50' : 'cursor-pointer' ?>">
                                  <input type="checkbox" name="seo_safe" value="1" class="sr-only peer" <?= (isset($config['seo_safe']) ? $config['seo_safe'] : 1) ? 'checked' : '' ?> <?= !$hasSeoSafe ? 'disabled' : '' ?>>
                                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500 transition-colors shadow-inner"></div>
                                </label>
                            </div>
                            
                            <!-- Biometria Facial AI (Liveness) -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 transition-colors hover:border-slate-700 relative overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-amber-500/10 rounded-full blur-xl pointer-events-none"></div>
                                <div>
                                    <h5 class="font-bold text-sm text-amber-400 flex items-center gap-2"><i class="ph-bold ph-camera"></i> Identidade Biométrica IA (Liveness)</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-lg">Zero-Knowledge Proof. Abre a câmera frontal do usuário e processa a face com IA na própria máquina simulando biometria antes de autorizar o acesso. (Sem coletar CPF ou dados sensíveis).</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer z-10">
                                  <input type="checkbox" name="age_estimation_active" value="1" class="sr-only peer" <?= (!isset($config['age_estimation_active']) || $config['age_estimation_active'] == 1) ? 'checked' : '' ?>>
                                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500 transition-colors shadow-inner border border-slate-700"></div>
                                </label>
                            </div>
                            
                            <!-- Blockchain -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 hover:border-slate-700 transition-colors">
                                <div>
                                    <h5 class="font-bold text-sm text-slate-200 flex items-center gap-2"><i class="ph-bold ph-database text-amber-500"></i> Custódia Forense na Edge</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-lg">Auditoria B2B. Todo visitante (aceite ou bloqueio) deve gerar um Hash SHA-256 no banco de dados Master do SaaS. Essencial para proteção Lei Procon/LGPD.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" checked disabled class="sr-only peer">
                                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600 transition-colors opacity-60"></div>
                                </label>
                            </div>
                            
                            <!-- Dados em Nuvem (Server Validation) -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 hover:border-slate-700 transition-colors">
                                <div>
                                    <h5 class="font-bold text-sm text-slate-200 flex items-center gap-2"><i class="ph-bold ph-server text-purple-400"></i> API Headless Ativa (Validação B2B)</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 max-w-lg">Quando ativo, as decisões de aceite são processadas no Servidor para maior robustez (e consomem cota da sua fatura comercial). Desativado atua apenas visualmente no navegador do usuário visitante.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" name="server_validation_active" value="1" class="sr-only peer" <?= (!isset($config['server_validation_active']) || $config['server_validation_active']) ? 'checked' : '' ?>>
                                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500 transition-colors shadow-inner"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="pt-6 border-t border-slate-800 mt-6 flex justify-end">
                    <button type="button" onclick="document.getElementById('frmSettings').requestSubmit();" id="btnSaveConfig" class="w-full md:w-auto bg-primary-600 hover:bg-primary-500 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-[0_4px_15px_rgba(99,102,241,0.2)] hover:shadow-[0_4px_20px_rgba(99,102,241,0.4)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                        <i class="ph-bold ph-cloud-arrow-up text-lg"></i> <span>Salvar Configurações WAF</span>
                    </button>
                    
                    <script>
                    function compileBlurSelector() {
                        const checks = document.querySelectorAll('#blur_checkbox_group input[type="checkbox"]:checked');
                        let selectors = Array.from(checks).map(el => el.value);
                        
                        const custom = document.getElementById('custom_blur_input')?.value.trim();
                        if (custom && custom.length > 0) {
                            selectors.push(custom);
                        }
                        
                        document.getElementById('blur_selector_input').value = selectors.join(', ');
                    }

                    function syncEdgeConfig(form) {
                        // Antes de submeter, unificamos os checkboxes do Blur!
                        compileBlurSelector();

                        const btn = document.getElementById('btnSaveConfig');
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-lg"></i> <span>Propagando na Edge Network...</span>';
                        btn.classList.add('opacity-80', 'cursor-not-allowed');
                        btn.disabled = true;
                        
                        const formData = new FormData(form);
                        formData.append('action', 'save_settings');
                        
                        fetch('?route=dashboard', { method: 'POST', body: formData })
                        .then(res => {
                            if(!res.ok) throw new Error("Erro de Servidor");
                            return res.json();
                        })
                        .then(data => {
                            if(data.error) throw new Error(data.error);
                            btn.innerHTML = '<i class="ph-bold ph-check text-lg"></i> <span>Configuração Sincronizada!</span>';
                            btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
                            btn.classList.add('bg-emerald-600', 'shadow-emerald-500/20');
                            
                            setTimeout(() => {
                                btn.innerHTML = originalHTML;
                                btn.classList.add('bg-primary-600', 'hover:bg-primary-500');
                                btn.classList.remove('bg-emerald-600', 'shadow-emerald-500/20', 'opacity-80', 'cursor-not-allowed');
                                btn.disabled = false;
                            }, 3500);
                        }).catch(err => {
                            btn.innerHTML = '<i class="ph-bold ph-warning text-lg"></i> <span>Falha ao Salvar</span>';
                            btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
                            btn.classList.add('bg-red-600', 'shadow-red-500/20');
                            FrontToast.show('error', "Falha de Integridade WAF: " + err.message);
                            setTimeout(() => {
                                btn.innerHTML = originalHTML;
                                btn.classList.add('bg-primary-600', 'hover:bg-primary-500');
                                btn.classList.remove('bg-red-600', 'shadow-red-500/20', 'opacity-80', 'cursor-not-allowed');
                                btn.disabled = false;
                            }, 3500);
                        });
                    }
                    </script>
                </div>
            </div>

            <!-- ====== TAB APPEARANCE (Personalização UI) ====== -->
            <div id="tab-appearance" class="tab-content max-w-5xl mx-auto">
                <div class="glass-panel p-8 rounded-2xl relative overflow-hidden mb-8 border border-pink-500/20">
                    <div class="absolute inset-0 bg-gradient-to-r from-pink-500/5 to-purple-500/5 pointer-events-none"></div>
                    <div class="flex items-start gap-6 relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center shrink-0 shadow-lg shadow-pink-500/20">
                            <i class="ph-bold ph-paint-brush-broad text-3xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-2 tracking-tight">Personalização de Marca e UI</h3>
                            <p class="text-sm text-slate-400 max-w-2xl leading-relaxed">Mapeie as cores nativas do seu site para que o Modal +18 e o Scanner Biométrico se fundam à sua marca perfeitamente. Forneça também as URLs das suas políticas jurídicas.</p>
                        </div>
                    </div>
                </div>

                <form id="frmAppearance" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Coluna UNIFICADA: Identidade Visual & Cores -->
                        <div class="lg:col-span-2 glass-panel p-8 rounded-2xl border border-slate-800 relative z-10 bg-gradient-to-br from-slate-900/90 to-[#0a0f18] shadow-lg">
                            <h4 class="font-bold text-white flex items-center gap-2 mb-2"><i class="ph-bold ph-palette text-pink-400 text-xl"></i> Identidade Visual do Ecossistema Front18</h4>
                            <p class="text-[10px] text-slate-400 mb-8 max-w-2xl uppercase tracking-widest">A paleta definida aqui será renderizada instantaneamente nos Banners LGPD, Formulários e nas Validações Biométricas dentro do seu domínio.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- BG COLOR -->
                                <div class="relative group cursor-pointer">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 pointer-events-none">Cor Base do Fundo</label>
                                    <div class="flex items-center gap-4 p-3 bg-slate-950/50 border border-slate-800 rounded-xl hover:border-slate-600 transition-all">
                                        <div class="relative w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 border-slate-700 shadow-inner group-hover:scale-105 transition-transform">
                                            <input type="color" name="color_bg" value="<?= htmlspecialchars($config['color_bg'] ?? '#0f172a') ?>" class="absolute -top-5 -left-5 w-24 h-24 cursor-pointer scale-150">
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-[9px] text-slate-500 font-bold uppercase mb-0.5">Background</div>
                                            <span class="text-xs font-mono text-white tracking-wider color-val-preview"><?= htmlspecialchars($config['color_bg'] ?? '#0f172a') ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- TEXT COLOR -->
                                <div class="relative group cursor-pointer">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 pointer-events-none">Tipografia (Contraste)</label>
                                    <div class="flex items-center gap-4 p-3 bg-slate-950/50 border border-slate-800 rounded-xl hover:border-slate-600 transition-all">
                                        <div class="relative w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 border-slate-700 shadow-inner group-hover:scale-105 transition-transform">
                                            <input type="color" name="color_text" value="<?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?>" class="absolute -top-5 -left-5 w-24 h-24 cursor-pointer scale-150">
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-[9px] text-slate-500 font-bold uppercase mb-0.5">Text & Icons</div>
                                            <span class="text-xs font-mono text-white tracking-wider color-val-preview"><?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- PRIMARY COLOR -->
                                <div class="relative group cursor-pointer">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 pointer-events-none">Cor de Destaque / Neon</label>
                                    <div class="flex items-center gap-4 p-3 bg-slate-950/50 border border-slate-800 rounded-xl shadow-[0_0_20px_color-mix(in_srgb,<?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>_20%,transparent)] hover:shadow-[0_0_30px_color-mix(in_srgb,<?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>_40%,transparent)] transition-all">
                                        <div class="relative w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 border-slate-600 shadow-inner group-hover:scale-105 transition-transform">
                                            <input type="color" name="color_primary" value="<?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>" class="absolute -top-5 -left-5 w-24 h-24 cursor-pointer scale-150">
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-[9px] text-slate-500 font-bold uppercase mb-0.5">Primary Accent</div>
                                            <span class="text-xs font-mono text-white tracking-wider color-val-preview"><?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $mConf = !empty($config['modal_config']) ? json_decode($config['modal_config'], true) : [];
                    $mTitle = $mConf['title'] ?? 'Conteúdo Protegido';
                    $mDesc = $mConf['desc'] ?? 'Este portal contém material comercial destinado exclusivamente para o público adulto. É necessário comprovar a sua tutela legal.';
                    $mYes = $mConf['btn_yes'] ?? 'Reconhecer e Continuar';
                    $mNo = $mConf['btn_no'] ?? 'Sou menor de idade (Sair)';
                    $camShape = $mConf['cam_shape'] ?? 'circle';
                    $camBorderColor = $mConf['cam_border_color'] ?? $config['color_primary'] ?? '#6366f1';
                    $camGlow = !empty($mConf['cam_glow']);
                    $modalBorderColor = $mConf['modal_border_color'] ?? '';
                    ?>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Coluna 3 Esquerda Flex: Textos & Câmera -->
                        <div class="flex flex-col gap-6">
                            
                            <!-- Box: Textos do Modal & Escape -->
                            <div class="glass-panel p-6 rounded-2xl border border-slate-800 relative z-10 w-full shadow-lg overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none"><i class="ph-fill ph-text-t text-9xl text-purple-500"></i></div>
                                <h4 class="font-bold text-white mb-6 flex items-center gap-2 relative z-10"><i class="ph-bold ph-text-t text-purple-400"></i> Localização Textual (Age Gate)</h4>
                                <div class="space-y-4 relative z-10">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Título de Bloqueio</label>
                                        <input type="text" id="live_modal_title" name="modal_title" value="<?= htmlspecialchars($mTitle) ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-purple-500 transition-all font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Breve Descrição Legal</label>
                                        <textarea id="live_modal_desc" name="modal_desc" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-sm text-slate-300 focus:outline-none focus:border-purple-500 transition-all font-mono custom-scrollbar"><?= htmlspecialchars($mDesc) ?></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Ação Positiva (Entrar)</label>
                                            <input type="text" id="live_modal_btn_yes" name="modal_btn_yes" value="<?= htmlspecialchars($mYes) ?>" class="w-full bg-emerald-900/10 border border-emerald-900/50 rounded-lg px-4 py-2.5 text-sm text-emerald-400 focus:outline-none focus:border-emerald-500 transition-all font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ação Negativa (Sair)</label>
                                            <input type="text" id="live_modal_btn_no" name="modal_btn_no" value="<?= htmlspecialchars($mNo) ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-400 focus:outline-none focus:border-slate-500 transition-all font-mono">
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-slate-800/50 mt-4">
                                        <label class="block text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> Destino da Fuga (Deny URL)</label>
                                        <input type="url" name="deny_url" placeholder="https://google.com" value="<?= htmlspecialchars($config['deny_url'] ?? '') ?>" class="w-full bg-rose-950/20 border border-rose-900/30 rounded-lg px-4 py-3 text-[11px] text-rose-200 focus:outline-none focus:border-rose-500 transition-all font-mono placeholder-rose-900/50">
                                        <p class="text-[9px] text-slate-500 mt-1 uppercase">Onde redirecionar os menores de idade rejeitados pela IA?</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Box: Arquitetura, Lentes e Borda do Modal -->
                            <div class="glass-panel p-6 rounded-2xl border border-slate-800 relative z-10 w-full shadow-lg overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none"><i class="ph-fill ph-camera text-9xl text-indigo-500"></i></div>
                                <h4 class="font-bold text-white mb-6 flex items-center gap-2 relative z-10"><i class="ph-bold ph-camera text-indigo-400"></i> Geometria e Ótica</h4>
                                
                                <div class="space-y-5 relative z-10">
                                    <!-- Câmera Shape Visual Selection -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Recorte Físico da Câmera (Avatar)</label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="cam_shape" value="circle" class="peer absolute opacity-0" <?= $camShape === 'circle' ? 'checked' : '' ?>>
                                                <div class="p-3 border border-slate-700/50 rounded-xl bg-slate-900/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:shadow-[0_0_15px_rgba(99,102,241,0.2)] flex flex-col items-center justify-center gap-2 transition-all">
                                                    <div class="w-10 h-10 rounded-full border-2 border-slate-600 peer-checked:border-indigo-400"></div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-500 peer-checked:text-indigo-400">Sphere</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="cam_shape" value="squircle" class="peer absolute opacity-0" <?= $camShape === 'squircle' ? 'checked' : '' ?>>
                                                <div class="p-3 border border-slate-700/50 rounded-xl bg-slate-900/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:shadow-[0_0_15px_rgba(99,102,241,0.2)] flex flex-col items-center justify-center gap-2 transition-all">
                                                    <div class="w-10 h-10 rounded-[12px] border-2 border-slate-600 peer-checked:border-indigo-400"></div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-500 peer-checked:text-indigo-400">Squircle</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="cam_shape" value="square" class="peer absolute opacity-0" <?= $camShape === 'square' ? 'checked' : '' ?>>
                                                <div class="p-3 border border-slate-700/50 rounded-xl bg-slate-900/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:shadow-[0_0_15px_rgba(99,102,241,0.2)] flex flex-col items-center justify-center gap-2 transition-all">
                                                    <div class="w-10 h-10 rounded-sm border-2 border-slate-600 peer-checked:border-indigo-400"></div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-500 peer-checked:text-indigo-400">Reto</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="cam_shape" value="rectangle" class="peer absolute opacity-0" <?= $camShape === 'rectangle' ? 'checked' : '' ?>>
                                                <div class="p-3 border border-slate-700/50 rounded-xl bg-slate-900/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 peer-checked:shadow-[0_0_15px_rgba(99,102,241,0.2)] flex flex-col items-center justify-center gap-2 transition-all">
                                                    <div class="w-[45px] h-8 rounded-md border-2 border-slate-600 peer-checked:border-indigo-400"></div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-500 peer-checked:text-indigo-400">Lands</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i class="ph-bold ph-aperture"></i> Lente (Highlight)</label>
                                            <div class="flex items-center gap-2 relative">
                                                <input type="color" id="live_cam_border_color" name="cam_border_color" value="<?= htmlspecialchars($camBorderColor) ?>" class="absolute top-0 left-0 w-10 h-10 opacity-0 cursor-pointer" oninput="document.getElementById('live_cam_border_hex').value = this.value; document.getElementById('live_cam_border_preview').style.backgroundColor = this.value;">
                                                <div id="live_cam_border_preview" class="w-10 h-10 rounded-lg border border-slate-700 pointer-events-none shrink-0" style="background-color: <?= htmlspecialchars($camBorderColor) ?>;"></div>
                                                <input type="text" id="live_cam_border_hex" value="<?= htmlspecialchars($camBorderColor) ?>" placeholder="#HEX" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-[11px] text-white uppercase focus:outline-none font-mono" oninput="document.getElementById('live_cam_border_color').value = this.value; document.getElementById('live_cam_border_preview').style.backgroundColor = this.value;">
                                            </div>
                                            <label class="flex items-center gap-2 cursor-pointer mt-3 bg-slate-900/50 px-2 py-2 rounded-lg border border-slate-800 hover:border-indigo-500/50 transition-colors">
                                                <input type="checkbox" id="live_cam_glow" name="cam_glow" value="1" <?= $camGlow ? 'checked' : '' ?> class="w-3 h-3 text-indigo-500 rounded bg-slate-800">
                                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Laser/Glow Emissor</span>
                                            </label>
                                        </div>
                                        
                                        <div>
                                            <label class="flex items-center gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><i class="ph-bold ph-frame-corners"></i> Borda do Age Gate</label>
                                            <div class="flex items-center gap-2 relative">
                                                <input type="color" id="live_modal_border_color" name="modal_border_color" value="<?= htmlspecialchars($modalBorderColor ?: '#ffffff') ?>" class="absolute top-0 left-0 w-10 h-10 opacity-0 cursor-pointer" oninput="document.getElementById('live_modal_border_hex').value = this.value; document.getElementById('live_modal_border_preview').style.backgroundColor = this.value; if(this.value){document.getElementById('mock_modal').style.borderColor=this.value;}else{document.getElementById('mock_modal').style.borderColor='rgba(255,255,255,0.08)';}">
                                                <div id="live_modal_border_preview" class="w-10 h-10 rounded-lg border border-slate-700 pointer-events-none shrink-0" style="background-color: <?= htmlspecialchars($modalBorderColor ?: 'transparent') ?>;">
                                                    <?= !$modalBorderColor ? '<div class="w-full h-full flex items-center justify-center text-slate-600 text-xs"><i class="ph-bold ph-prohibit"></i></div>' : '' ?> 
                                                </div>
                                                <input type="text" id="live_modal_border_hex" value="<?= htmlspecialchars($modalBorderColor) ?>" placeholder="VAZIO = PADRÃO" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-2 py-2 text-[10px] text-white uppercase focus:outline-none font-mono" oninput="document.getElementById('live_modal_border_color').value = this.value; document.getElementById('live_modal_border_preview').style.backgroundColor = this.value  || 'transparent'; if(this.value){document.getElementById('mock_modal').style.borderColor=this.value;}">
                                            </div>
                                            <p class="text-[9px] text-slate-500 mt-2 font-mono">Apague p/ usar borda NATIVA.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna 4: Live Preview do Ecossistema -->
                        <div class="glass-panel p-6 rounded-2xl border border-slate-800 flex flex-col items-center justify-center relative overflow-hidden bg-black/40 shadow-inner min-h-[500px]">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(99,102,241,0.05)_0%,transparent_70%)]" id="preview_bg"></div>
                            <h4 class="absolute top-4 left-6 font-bold text-slate-400 text-[10px] uppercase tracking-widest flex items-center gap-2"><i class="ph-bold ph-magic-wand text-primary-400"></i> Component View (CSS Rendering)</h4>
                            
                            <!-- Container Flex para evitar sobreposição -->
                            <div class="w-full h-full flex flex-col items-center justify-start gap-8 relative z-10 py-8 overflow-y-auto max-h-[700px]">
                            
                            <!-- O Mock do Modal -->
                            <?php $tempMockBorder = $modalBorderColor ?: "rgba(255,255,255,0.08)"; ?>
                            <div id="mock_modal" style="zoom: 0.70; background: <?= htmlspecialchars($config['color_bg'] ?? '#0f172a') ?>; border: 2px solid <?= htmlspecialchars($tempMockBorder) ?>; color: <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?>;" class="relative z-10 p-[48px_40px] rounded-[24px] text-center w-full max-w-[460px] shadow-[0_0_40px_rgba(0,0,0,0.5)] transition-all font-sans shrink-0">
                                <div id="mock_badge" style="background: color-mix(in srgb, <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?> 15%, transparent); color: <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>; border: 1px solid color-mix(in srgb, <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?> 30%, transparent);" class="inline-flex items-center justify-center px-[14px] py-[6px] rounded-[20px] text-[11px] font-bold tracking-[0.5px] uppercase mb-[24px]">
                                    <img src="/public/img/favicon.png" style="width:16px; height:16px; margin-right:6px; object-fit:contain; filter:brightness(2) drop-shadow(0 0 2px rgba(255,255,255,0.5));" onerror="this.style.display='none'">
                                    RESTRIÇÃO DE IDADE
                                </div>
                                <h2 id="mock_title" class="text-[26px] font-[800] mb-[16px] tracking-[-0.5px]"><?= htmlspecialchars($mTitle) ?></h2>
                                <p id="mock_desc" style="opacity: 0.7;" class="text-[15px] mb-[32px] leading-[1.6]"><?= htmlspecialchars($mDesc) ?><br><a href="#" id="mock_link_help" style="color: <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>; filter:brightness(1.5); font-size:12px; font-weight:bold; display:inline-block; margin-top:10px; text-decoration:none; opacity: 1;">[?] Como a Tecnologia protege sua Privacidade</a></p>
                                
                                <div id="mock_legal" style="background: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 5%, transparent); border-color: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent);" class="text-left p-[20px] rounded-[16px] border flex items-start gap-[16px] mb-[30px] transition-colors">
                                    <div id="mock_check_box" style="background: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent); border-color: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 20%, transparent);" class="w-[20px] h-[20px] border-[2px] rounded-[6px] flex items-center justify-center shrink-0 mt-[2px] transition-colors"></div>
                                    <div style="opacity: 0.8;" class="text-[13px] leading-[1.6]">
                                        Declaro categoricamente ser <b>maior de 18 anos</b> e concordo integralmente com os <a href="#" id="mock_link_terms" style="color: <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>; font-weight: 600; opacity: 1;">Termos de Serviço</a> e a rigorosa <a href="#" id="mock_link_privacy" style="color: <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>; font-weight: 600; opacity: 1;">Política de Privacidade</a>.
                                    </div>
                                </div>

                                <div class="flex flex-col gap-[12px]">
                                    <button id="mock_btn_yes" type="button" style="background: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 20%, transparent); color: <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?>; border: 1px solid color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent);" class="w-full py-[16px] px-[20px] rounded-[12px] text-[15px] font-[600] shadow-none cursor-not-allowed opacity-[0.5]">
                                        <?= htmlspecialchars($mYes) ?>
                                    </button>
                                    <button id="mock_btn_no" type="button" style="border-color: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 20%, transparent); opacity: 0.7;" class="w-full py-[16px] px-[20px] rounded-[12px] text-[15px] font-[600] border transition-all">
                                        <?= htmlspecialchars($mNo) ?>
                                    </button>
                                </div>
                                
                                <div id="mock_footer" style="border-top-color: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent); opacity: 0.4;" class="mt-[32px] pt-[20px] border-t text-[11px] leading-[1.6]">
                                    <strong style="opacity: 0.7;" class="font-[700] tracking-[0.5px]">NÚCLEO DE MITIGAÇÃO JURÍDICA</strong><br>
                                    Barreira funcional dotada de registro inviolável em Blockchain.<br>
                                    <span id="mock_footer_badge" style="background: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent); border-color: color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 15%, transparent); opacity: 0.8;" class="inline-block mt-[8px] px-[8px] py-[4px] border rounded-[6px] font-mono text-[10px]">Contrato Base: v1.0-2026</span>
                                </div>
                            </div>

                            <!-- O Mock REAL do Banner DPO (Estilo Front18 SDK Card) -->
                            <div id="mock_dpo" style="zoom: 0.75; background: <?= htmlspecialchars($config['color_bg'] ?? '#0f172a') ?>; color: <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?>; border: 1px solid color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 10%, transparent);" class="relative z-10 w-full max-w-[340px] p-[24px] rounded-[16px] shadow-[0_20px_40px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.05)] flex flex-col font-sans transition-all shrink-0">
                                <div class="flex items-center justify-between mb-[12px]">
                                    <div class="font-[800] text-[14px] flex items-center gap-[6px]">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                        Aviso de Privacidade LGPD
                                    </div>
                                    <div class="flex items-center gap-[8px]">
                                        <div style="opacity:0.5"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
                                        <div class="flex items-center justify-center w-[28px] h-[28px] rounded-[6px] bg-yellow-500/15 border-[2px] border-yellow-500 text-yellow-500 font-[900] text-[10px] tracking-[-0.5px]">18</div>
                                    </div>
                                </div>
                                
                                <div class="text-[12px] leading-[1.6] opacity-70 mb-[16px]">
                                    Nosso Cérebro processa dados para finalidades ligadas ao fornecimento da plataforma e segurança antifraude.
                                </div>
                                
                                <div class="flex flex-col gap-[8px]">
                                    <button id="mock_dpo_btn_yes" type="button" style="background: <?= htmlspecialchars($config['color_primary'] ?? '#6366f1') ?>; color: #ffffff;" class="w-[100%] p-[12px] rounded-[8px] text-[12px] font-[600] shadow-[inset_0_1px_0_rgba(255,255,255,0.2)]">
                                        Autorizar & Continuar
                                    </button>
                                    <button id="mock_dpo_btn_no" type="button" style="border: 1px solid color-mix(in srgb, <?= htmlspecialchars($config['color_text'] ?? '#f8fafc') ?> 20%, transparent); opacity:0.6;" class="w-[100%] p-[12px] rounded-[8px] text-[12px] font-[600] text-inherit bg-transparent">
                                        Apenas Essenciais
                                    </button>
                                </div>
                                
                                <div class="flex justify-center gap-[16px] mt-[16px] text-[10px] opacity-50">
                                    <span style="color:inherit;text-decoration:none">Denúncia DPO</span>
                                    <span style="color:inherit;text-decoration:none">Políticas</span>
                                    <span style="color:inherit;text-decoration:none">Termos</span>
                                </div>
                            </div>
                            
                            </div> <!-- Fecha Flex Wrapper -->
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800 mt-6 flex justify-end">
                        <button type="button" onclick="syncAppearanceConfig(document.getElementById('frmAppearance'))" id="btnSaveAppearance" class="w-full md:w-auto bg-pink-600 hover:bg-pink-500 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-[0_4px_15px_rgba(236,72,153,0.2)] hover:shadow-[0_4px_20px_rgba(236,72,153,0.4)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                            <i class="ph-bold ph-floppy-disk text-lg"></i> <span>Aplicar Design</span>
                        </button>
                    </div>
                </form>

                <!-- Script de Update Ajax para o Appearance -->
                <script>
                // Atualiza o display das cores quando muda e atualiza o preview visual
                document.querySelectorAll('input[type="color"]').forEach(input => {
                    input.addEventListener('input', (e) => {
                        const displaySpan = e.target.closest('.group')?.querySelector('.color-val-preview');
                        if(displaySpan) displaySpan.textContent = e.target.value;
                        const v = e.target.value;
                        if (e.target.name === 'color_bg') {
                            document.getElementById('mock_modal').style.background = v;
                            document.getElementById('mock_dpo').style.background = v;
                        } else if (e.target.name === 'color_text') {
                            document.getElementById('mock_modal').style.color = v;
                            document.getElementById('mock_btn_yes').style.background = `color-mix(in srgb, ${v} 20%, transparent)`;
                            document.getElementById('mock_btn_yes').style.color = v;
                            document.getElementById('mock_btn_yes').style.borderColor = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_btn_no').style.borderColor = `color-mix(in srgb, ${v} 20%, transparent)`;
                            document.getElementById('mock_legal').style.background = `color-mix(in srgb, ${v} 5%, transparent)`;
                            document.getElementById('mock_legal').style.borderColor = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_check_box').style.background = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_check_box').style.borderColor = `color-mix(in srgb, ${v} 20%, transparent)`;
                            document.getElementById('mock_footer').style.borderTopColor = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_footer_badge').style.background = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_footer_badge').style.borderColor = `color-mix(in srgb, ${v} 15%, transparent)`;
                            // DPO Elements
                            document.getElementById('mock_dpo').style.color = v;
                            document.getElementById('mock_dpo').style.borderColor = `color-mix(in srgb, ${v} 10%, transparent)`;
                            document.getElementById('mock_dpo_btn_no').style.borderColor = `color-mix(in srgb, ${v} 20%, transparent)`;
                        } else if (e.target.name === 'color_primary') {
                            document.getElementById('mock_badge').style.color = v;
                            document.getElementById('mock_badge').style.background = `color-mix(in srgb, ${v} 15%, transparent)`;
                            document.getElementById('mock_badge').style.borderColor = `color-mix(in srgb, ${v} 30%, transparent)`;
                            document.getElementById('mock_link_help').style.color = v;
                            document.getElementById('mock_link_terms').style.color = v;
                            document.getElementById('mock_link_privacy').style.color = v;
                            // DPO Elements
                            document.getElementById('mock_dpo_btn_yes').style.background = v;
                        }
                    });
                });

                // Live Preview dos Textos
                const lpmap = {
                    'live_modal_title': 'mock_title',
                    'live_modal_desc': 'mock_desc',
                    'live_modal_btn_yes': 'mock_btn_yes',
                    'live_modal_btn_no': 'mock_btn_no'
                };
                for (let inputId in lpmap) {
                    document.getElementById(inputId).addEventListener('input', (e) => {
                        document.getElementById(lpmap[inputId]).textContent = e.target.value;
                    });
                }

                function syncAppearanceConfig(form) {
                    const btn = document.getElementById('btnSaveAppearance');
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-lg"></i> <span>Atualizando SDK...</span>';
                    btn.classList.add('opacity-80', 'cursor-not-allowed');
                    btn.disabled = true;
                    
                    const formData = new FormData(form);
                    formData.append('action', 'save_appearance');
                    
                    fetch('?route=dashboard', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        btn.innerHTML = '<i class="ph-bold ph-check text-lg"></i> <span>Design Publicado!</span>';
                        btn.classList.remove('bg-pink-600', 'hover:bg-pink-500');
btn.classList.add('bg-emerald-600', 'shadow-emerald-500/20');
                        
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.classList.add('bg-pink-600', 'hover:bg-pink-500');
                            btn.classList.remove('bg-emerald-600', 'shadow-emerald-500/20', 'opacity-80', 'cursor-not-allowed');
                            btn.disabled = false;
                        }, 3500);
                    });
                }
                </script>
            </div>

            <!-- TAB: PORTAL LGPD & COOKIES -->
            <div id="tab-privacy" class="tab-content max-w-5xl mx-auto">
                
                <!-- HEADER & MATURITY SCORE -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="md:col-span-2 glass-panel p-8 rounded-2xl relative overflow-hidden border border-emerald-500/20">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-teal-500/5 pointer-events-none"></div>
                        <div class="flex items-start gap-6 relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/20">
                                <i class="ph-bold ph-scales text-3xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white mb-2 tracking-tight">Portal DPO & Compliance (LGPD)</h3>
                                <p class="text-sm text-slate-400 max-w-2xl leading-relaxed mb-4">Painel de gerenciamento corporativo alinhado com a Lei nº 13.709/2018 (LGPD), Marco Civil da Internet e resoluções da ANPD aplicáveis ao ecossistema Front18.</p>

                                <details class="group cursor-pointer">
                                    <summary class="text-[10px] font-bold uppercase tracking-widest text-emerald-400 flex items-center gap-1 hover:text-emerald-300 transition w-fit list-none">
                                        <i class="ph-bold ph-info"></i> Como o Algoritmo Funciona?
                                        <i class="ph-bold ph-caret-down group-open:rotate-180 transition-transform ml-1 text-slate-500"></i>
                                    </summary>
                                    <div class="mt-3 p-4 bg-slate-900/80 border border-slate-800 rounded-xl text-[10px] text-slate-400 space-y-2 leading-relaxed max-w-2xl backdrop-blur-sm cursor-text shadow-lg shadow-black/50">
                                        <p><strong class="text-white">+40 Pts (Base Front18):</strong> Concedidos automaticamente pelo uso contínuo da nossa arquitetura Zero-Trust, WAF e eliminação de RAM após reconhecimento biométrico/facial ou validações complexas.</p>
                                        <p><strong class="text-white">+20 Pts (Nomeação Legal):</strong> Preencha um "E-mail de Contato de Privacidade (DPO)" nas configurações da aba [Consentimento].</p>
                                        <p><strong class="text-white">+25 Pts (Direito de Cancelar):</strong> Habilite o check 'Botão Visível?' para Ação de Revogação no seu Banner Flutuante de Consentimento. (Adequação direta com o Art. 18 de interrupção de tratamentos).</p>
                                        <p><strong class="text-white">+15 Pts (Finalidade Explícita):</strong> Escreva um Aviso de Consentimento estruturado (com mais de 30 letras). Evite frases genéricas prontas da internet.</p>
                                        <div class="mt-3 pt-3 border-t border-slate-700/50 flex flex-wrap gap-4 text-[9px] uppercase font-bold tracking-widest">
                                            <span class="text-emerald-500"><i class="ph-fill ph-check-circle"></i> Risco Baixo (Acima de 90)</span>
                                            <span class="text-amber-500"><i class="ph-fill ph-warning"></i> Médio (Acima de 65)</span>
                                            <span class="text-rose-500"><i class="ph-fill ph-warning-octagon"></i> Alto (Abaixo de 65)</span>
                                        </div>
                                    </div>
                                </details>

                            </div>
                        </div>
                    </div>
                <?php
                $privConf = !empty($config['privacy_config']) ? json_decode($config['privacy_config'], true) : [];
                $dpoEmail = $privConf['dpo_email'] ?? '';
                $dpoTitle = $privConf['dpo_title'] ?? 'DPO Officer';
                $bannerTitle = $privConf['banner_title'] ?? 'Aviso de Privacidade e LGPD';
                $bannerText = $privConf['banner_text'] ?? 'Utilizamos cookies essenciais e avaliativos para garantir o funcionamento seguro deste portal. Ao ignorar, você assina implicitamente que está ciente da vigilância digital.';
                $btnAccept = $privConf['btn_accept'] ?? 'Aceitar Essenciais e Continuar';
                $btnReject = $privConf['btn_reject'] ?? 'Rejeitar Opcionais';
                
                // Calculadora Dinâmica de Maturidade LGPD
                $lgpdScore = 40; // O simples uso da arquitetura Zero-Trust do Front18 garante 40 pt base
                if (!empty($dpoEmail)) $lgpdScore += 20; // DPO Público e Acessível (+20)
                if (isset($privConf['allow_reject']) && $privConf['allow_reject']) $lgpdScore += 25; // Botão de Op-out/Revogação visível (+25)
                if (!empty($privConf['banner_text']) && strlen($privConf['banner_text']) > 30) $lgpdScore += 15; // Finalidade Transparente e explícita (+15)
                
                $scoreColor = $lgpdScore >= 90 ? 'emerald' : ($lgpdScore >= 65 ? 'amber' : 'rose');
                $riskLevel = $lgpdScore >= 90 ? 'Baixo' : ($lgpdScore >= 65 ? 'Médio' : 'Alto');
                ?>
                
                    <div class="glass-panel p-6 rounded-2xl border border-slate-800 flex flex-col justify-center items-center text-center relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-<?= $scoreColor ?>-500/10 rounded-full blur-2xl transition-colors duration-1000"></div>
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2">Maturidade LGPD</p>
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-4xl font-black text-white"><?= $lgpdScore ?></span><span class="text-lg text-slate-500 font-bold pb-1">/100</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2 mb-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-<?= $scoreColor ?>-600 to-<?= $scoreColor ?>-400 h-1.5 rounded-full transition-all duration-1000" style="width: <?= $lgpdScore ?>%"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="flex w-2 h-2 rounded-full bg-<?= $scoreColor ?>-500 shadow-[0_0_8px_rgba(var(--color-<?= $scoreColor ?>-500),0.8)]"></span>
                            <span class="text-[11px] text-<?= $scoreColor ?>-400 font-bold">Risco Nível: <?= $riskLevel ?></span>
                        </div>
                    </div>
                </div>

                <!-- NAV PILLS PARA OS SUB-MÓDULOS -->
                <div class="flex flex-wrap gap-2 mb-8 border-b border-slate-800 pb-4">
                    <button type="button" id="btn-dpo-consent" onclick="switchDpoTab('consent')" class="dpo-tab-btn px-4 py-2 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold uppercase tracking-widest transition"><i class="ph-bold ph-shield-check mr-1"></i> Consentimento & Banner</button>
                    
                    <button type="button" id="btn-dpo-docs" onclick="switchDpoTab('docs')" class="dpo-tab-btn px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition text-xs font-bold uppercase tracking-widest"><i class="ph-bold ph-link mr-1"></i> Hub de Documentos</button>
                    
                    <button type="button" id="btn-dpo-reports" onclick="switchDpoTab('reports')" class="dpo-tab-btn px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition text-xs font-bold uppercase tracking-widest"><i class="ph-bold ph-activity mr-1"></i> RIPD & Relatórios</button>
                    
                    <button type="button" id="btn-dpo-learn" onclick="switchDpoTab('learn')" class="dpo-tab-btn px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition text-xs font-bold uppercase tracking-widest"><i class="ph-bold ph-graduation-cap mr-1"></i> Conhecendo a Lei</button>
                </div>

                <div id="dpo-panels-wrapper">

                    <!-- TAB 5: CONHECENDO A LEI -->
                    <div id="dpo-panel-learn" class="dpo-panel hidden">
                        <div class="glass-panel p-8 rounded-2xl border border-blue-500/20 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5"><i class="ph-fill ph-scales text-9xl text-blue-500"></i></div>
                            <h4 class="text-2xl font-bold text-white mb-2 flex items-center gap-3 relative z-10"><i class="ph-bold ph-graduation-cap text-blue-400"></i> Descomplicando o Compliance Jurídico</h4>
                            <p class="text-slate-400 max-w-3xl leading-relaxed mb-8 relative z-10">O objetivo desta seção não é substituir um time de advogados, mas te dar total autonomia sobre as ferramentas técnicas que o Front18 entrega para provar sua boa-fé em caso de litígio ou fiscalização da ANPD.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                                
                                <!-- COLUNA 1 -->
                                <div class="space-y-6">
                                    <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-800">
                                        <h5 class="text-lg font-bold text-white mb-2 flex items-center gap-2"><i class="ph-fill ph-book-open text-emerald-500"></i> 1. O Básico (LGPD e Marco Civil)</h5>
                                        <p class="text-xs text-slate-400 leading-relaxed mb-3">Toda vez que você coleta IP ou cookies, você está lidando com Dados Pessoais de acordo com a <strong class="text-slate-300">Lei nº 13.709/2018</strong>.</p>
                                        <ul class="text-[11px] text-slate-500 space-y-2 list-disc pl-4">
                                            <li><strong class="text-slate-300">Marco Civil (Art. 15):</strong> Exige a guarda dos logs de acesso (IP, hora e data) por pelo menos 6 meses para eventuais ordens judiciais. O Front18 garante essa criptografia sem expor os dados.</li>
                                            <li><strong class="text-slate-300">Consentimento:</strong> O Front18 injeta o Banner para autorizar Rastreadores (Analytics/Ads). Se o usuário disser "Não", nós bloqueamos as Tags para você não ser multado.</li>
                                        </ul>
                                    </div>

                                    <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-800">
                                        <h5 class="text-lg font-bold text-white mb-2 flex items-center gap-2"><i class="ph-fill ph-prohibit text-rose-500"></i> 2. Restrição de Idade & Lei 15.211</h5>
                                        <p class="text-xs text-slate-400 leading-relaxed mb-3">O mito de que "É só colocar um botão 'Tenho +18'" caiu por terra com legislações locais que exigem dupla validação e diligência comprovada.</p>
                                        <ul class="text-[11px] text-slate-500 space-y-2 list-disc pl-4">
                                            <li>O Front18 AgeGate usa modelos estruturais (sem guardar fotos) para forçar o upload de prova fática.</li>
                                            <li>Se acionado, você terá como provar que aplicou um obstáculo tecnológico complexo — ao contrário de redes abertas que apenas perguntam a data de nascimento.</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- COLUNA 2 -->
                                <div class="space-y-6">
                                    <div class="bg-blue-900/10 p-6 rounded-xl border border-blue-500/20">
                                        <h5 class="text-lg font-bold text-blue-400 mb-2 flex items-center gap-2"><i class="ph-fill ph-shield-check text-blue-500"></i> Como o Front18 te Protege</h5>
                                        <p class="text-xs text-blue-200/60 leading-relaxed mb-4">Nenhuma plataforma online pode prometer ausência de processos. Nossa promessa é blindagem técnica: entregar logs auditáveis (como num painel de avião) para você nunca estar de mãos atarefadas numa defesa.</p>
                                        
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3 bg-slate-900 rounded-lg p-3">
                                                <i class="ph-fill ph-trash text-emerald-500 text-xl shrink-0 mt-0.5"></i>
                                                <div>
                                                    <strong class="text-xs text-white block">RAM Purge (Zero Redundância)</strong>
                                                    <span class="text-[10px] text-slate-400">Processamos imagens sensíveis sem gravar no disco (SSD). No fim do looping, a memória RAM é expurgada (Garbage Collection nativa).</span>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 bg-slate-900 rounded-lg p-3">
                                                <i class="ph-fill ph-fingerprint text-emerald-500 text-xl shrink-0 mt-0.5"></i>
                                                <div>
                                                    <strong class="text-xs text-white block">Tokens Hasheados (Anonimização)</strong>
                                                    <span class="text-[10px] text-slate-400">O usuário do seu site é um número randômico, irreversível e criptografado (`bcrypt`). Se o banco de dados for exposto, nenhum dado pessoal será legível.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-amber-900/10 p-4 rounded-xl border border-amber-500/20 flex items-start gap-4">
                                        <i class="ph-fill ph-warning-circle text-amber-500 text-3xl shrink-0"></i>
                                        <div>
                                            <strong class="text-sm text-amber-400 block mb-1">Aviso de Diligência Legal</strong>
                                            <p class="text-[10px] text-amber-200/50 leading-relaxed">Você (SaaS / Publisher) é o Controlador de Dados (Art. 5º VI). O Front18 atua apenas como Operador Técnico de Blindagem (Art. 5º VII). Preencha seus Termos de Uso e declare isso sempre que solicitado.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 2: CENTRAL DE LINKS JURÍDICOS E TERMOS -->
                    <div id="dpo-panel-docs" class="dpo-panel hidden">
                        <form id="frmPrivacyDocs" class="space-y-6">
                            
                            <!-- Formulário de URLs Públicas -->
                            <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                                <h4 class="font-bold text-white mb-2 flex items-center gap-2"><i class="ph-bold ph-link text-indigo-400"></i> Hub de Documentos Públicos</h4>
                                <p class="text-[10px] text-slate-400 mb-6 leading-relaxed">Hospede suas políticas no seu próprio site. Cole os links abaixo para o Front18 injetá-los automaticamente nos avisos e pop-ups.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2"><i class="ph-bold ph-shield text-indigo-400"></i> Política de Privacidade</label>
                                        <div class="relative">
                                            <i class="ph-bold ph-link absolute left-3 top-3 text-slate-500"></i>
                                            <input type="url" name="privacy_url" placeholder="https://seusite.com/privacidade" value="<?= htmlspecialchars($privConf['privacy_url'] ?? '') ?>" class="bg-slate-900 border border-slate-700 pl-9 rounded-lg px-4 py-3 text-[11px] text-white w-full focus:border-indigo-500 focus:outline-none placeholder-slate-600">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2"><i class="ph-bold ph-file-text text-sky-400"></i> Termos de Uso</label>
                                        <div class="relative">
                                            <i class="ph-bold ph-link absolute left-3 top-3 text-slate-500"></i>
                                            <input type="url" name="terms_url" placeholder="https://seusite.com/termos" value="<?= htmlspecialchars($privConf['terms_url'] ?? '') ?>" class="bg-slate-900 border border-slate-700 pl-9 rounded-lg px-4 py-3 text-[11px] text-white w-full focus:border-sky-500 focus:outline-none placeholder-slate-600">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2"><i class="ph-bold ph-list-dashes text-emerald-400"></i> Formulário DPO / Titular</label>
                                        <div class="relative">
                                            <i class="ph-bold ph-link absolute left-3 top-3 text-slate-500"></i>
                                            <input type="url" name="rights_url" placeholder="https://seusite.com/direitos-lgpd" value="<?= htmlspecialchars($privConf['rights_url'] ?? '') ?>" class="bg-slate-900 border border-slate-700 pl-9 rounded-lg px-4 py-3 text-[11px] text-white w-full focus:border-emerald-500 focus:outline-none placeholder-slate-600">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2"><i class="ph-bold ph-cookie text-amber-400"></i> Política de Cookies</label>
                                        <div class="relative">
                                            <i class="ph-bold ph-link absolute left-3 top-3 text-slate-500"></i>
                                            <input type="url" name="cookies_url" placeholder="https://seusite.com/cookies" value="<?= htmlspecialchars($privConf['cookies_url'] ?? '') ?>" class="bg-slate-900 border border-slate-700 pl-9 rounded-lg px-4 py-3 text-[11px] text-white w-full focus:border-amber-500 focus:outline-none placeholder-slate-600">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-start pt-4 border-t border-slate-800">
                                    <button type="button" onclick="syncPrivacyConfig(this, this.closest('form'))" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-black text-[10px] shadow-[0_4px_15px_rgba(79,70,229,0.2)] hover:shadow-indigo-500/40 transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                                        <i class="ph-bold ph-floppy-disk text-sm"></i> <span>Salvar Hub de Links</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Repositório de Modelos para o Cliente -->
                            <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                                <h4 class="font-bold text-white mb-2 flex items-center gap-2"><i class="ph-bold ph-folder-open text-slate-400"></i> Templates Base de Conformidade Front18</h4>
                                <p class="text-[10px] text-slate-400 mb-6 leading-relaxed">Você pode copiar as matrizes de proteção abaixo e construir suas próprias páginas no WordPress, inserindo os links finalizados ali em cima.</p>
                                
                                <div class="space-y-3">
                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-file-text text-indigo-400"></i> Política de Privacidade Web</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-64 overflow-y-auto custom-scrollbar relative">
                                        <button class="absolute top-2 right-2 text-slate-500 hover:text-indigo-400" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); FrontToast.show('success', 'Texto copiado com sucesso para sua área de transferência!');"><i class="ph-bold ph-copy text-lg"></i></button>
                                        <div class="whitespace-pre-wrap">1. Identificação do Controlador (Art.9 III)
Controlador de Dados: Razão Social Ltda
CNPJ: 00.000.000/0001-00
Site: https://meusite.com.br
Encarregado de Dados (DPO) — Art.41: Nome do Encarregado / dpo@seudominio.com.br

2. Dados Pessoais que Coletamos (Art.9 I)
O site coleta o mínimo de dados necessários para cumprir regras de proteção de menores (restrição 18+). Abaixo detalhamos cada dado:
- Endereço IP (hash): Segurança e anti-fraude (Art.7 IX — Legítimo interesse). Hash irreversível.
- Foto de documento: Verificação de idade (Art.11 I — Consentimento + Art.7 II — Obrigação legal). Eliminada imediatamente.
- Data de nascimento: Cálculo de idade (Art.7 II). Não armazenada.
- Faixa etária: Controle de acesso. Dado anonimizado temporário de sessão.
- Dados de denúncia: Canal de denúncias de violações (Art.7 II). Conforme prazo legal.

🔒 Privacy by Design (Art.46 §2): O sistema foi projetado para coletar o mínimo necessário. A verificação é processada em memória e eliminada (...nunca é salva em disco).

3. Finalidades do Tratamento (Art.6 I, Art.9 I)
Os dados pessoais são tratados para Verificação de idade, Prevenção de fraudes e manutenção do Canal de denúncias.
⛔ Não criamos perfis comportamentais, não compartilhamos dados para marketing cruzado e não vendemos dados pessoais.

4. Compartilhamento (Art.9 V) e Seus Direitos (Art.18)
O compartilhamento é feito estritamente quando exigido por obrigações legais em denúncias.
Você tem direito à Confirmação, Acesso, Correção, Anonimização, Portabilidade, Eliminação e Revogação (Art.18). Acesse em "Seus Direitos (LGPD)".</div>
                                    </div>
                                </details>

                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-file-doc text-sky-400"></i> Termos de Uso (Menores)</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-48 overflow-y-auto custom-scrollbar relative">
                                        <button class="absolute top-2 right-2 text-slate-500 hover:text-sky-400" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); FrontToast.show('success', 'Texto copiado com sucesso para sua área de transferência!');"><i class="ph-bold ph-copy text-lg"></i></button>
                                        <div class="whitespace-pre-wrap">1. Aceitação dos Termos
Ao acessar o site "Meu Site", você concorda com estes Termos de Uso e com nossa Política de Privacidade.

2. Restrição de Idade (Classificação Indicativa: 18+)
Este site contém conteúdo restrito a maiores de 18 anos. Para acessar o conteúdo, é obrigatória verificação rigorosa por meio de métodos que comprovem a sua identidade, não bastando autodeclaração, servindo para adequar a proteção digital.

3. Restrições do Usuário
Você confirma responsabilizar-se legalmente e agir em premissa de titular primário de seus dispositivos...</div>
                                    </div>
                                </details>

                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-database text-purple-400"></i> ROPA (Registro de Tratamento)</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-64 overflow-y-auto custom-scrollbar relative">
                                        <button class="absolute top-2 right-2 text-slate-500 hover:text-purple-400" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); FrontToast.show('success', 'Texto copiado com sucesso para sua área de transferência!');"><i class="ph-bold ph-copy text-lg"></i></button>
                                        <div class="whitespace-pre-wrap">Registro de Atividades de Tratamento (ROPA) — LGPD Art.37
1. Identificação do Controlador
Razão Social: Razão Social Ltda
Site: https://meusite.com.br
DPO: <?= htmlspecialchars($dpoEmail) ?>

2. Operações de Tratamento Contempladas
- Verificação de idade: Foto / Base: Art.11 I + Art.7 II / Extrair data / Armazenamento: RAM / Retenção: Imediata.
- Controle de Sessão: Token / Base: Art.7 IX / Impedir fraudes / Retenção: 30 dias na BD de tokens.
- Proteção WAF anti-fraude: IP (hash) / Base: Art.7 IX / Prevenção / Retenção 6 meses.
- Canal de denúncias: Nome, Email, Ticket / Base: Art.7 II / Investigar falhas / Retenção até prescrição.
- Auditoria do Sistema Front18: Eventos Adminsitrativos (Sem PII de Usuários) / Base: Dec. Regulatório Marco Civil, Art. 15. / Retenção: 1 Ano.

✅ Medida de minimização atestada pelo Front18 Shield: Fotos de documentos jamais persistem em banco de dados. Uso exclusivo do RAM para processar liberação de interface.</div>
                                    </div>
                                </details>

                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-target text-pink-400"></i> RIPD (Relatório de Impacto)</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-64 overflow-y-auto custom-scrollbar relative">
                                        <button class="absolute top-2 right-2 text-slate-500 hover:text-pink-400" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); FrontToast.show('success', 'Texto copiado com sucesso para sua área de transferência!');"><i class="ph-bold ph-copy text-lg"></i></button>
                                        <div class="whitespace-pre-wrap">Relatório de Impacto à Proteção de Dados (RIPD) — LGPD Art.38
1. Descrição do Tratamento
Processo em Avaliação: Processamento de mídias restritivas para garantir acesso somente para 18+.
Dados em risco: Mídias de checagem.
Controlador: Razão Social Ltda

2. Necessidade e Proporcionalidade (Finalidade Legítima)
A coleta é exigida por dever de compliance e proteção estatutária. Da checagem em memória, o Front18 retém exclusivamente um booleano `18+` de aprovação criptografado, não persistindo atributos descritivos do titular.

3. Medidas Mitigadoras de Risco (Defesa Front18)
- Zero Trust e Proteção Criptográfica na Ponta (Client-side validation restrita).
- Prevenção de Scraping: Impedimento contra raspagem automatizada em escala, reduzindo o vetor de ataque aos logs.
- Flush Imediato (Garbage Collection): Garantia programática de limpeza do buffer da câmera instantaneamente na mesma rotina que calcula a idade.</div>
                                    </div>
                                </details>

                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-list-dashes text-emerald-400"></i> Formulário "Seus Direitos"</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-64 overflow-y-auto custom-scrollbar relative">
                                        <button class="absolute top-2 right-2 text-slate-500 hover:text-emerald-400" onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); FrontToast.show('success', 'Texto copiado com sucesso para sua área de transferência!');"><i class="ph-bold ph-copy text-lg"></i></button>
                                        <div class="whitespace-pre-wrap">📋 Central de Direitos (LGPD)
Exerça seus direitos como Titular de Dados conforme Art.18 da Lei 13.709/2018 (LGPD).
Lembramos que o Front18 Shield coleta o mínimo de dados possível. PII's efêmeros não são armazenados (não é possível exportar documentos antigos não salvos). 

[Selecione Categoria de Risco]
- Confirmação de Existência de Tratamento (Art.18 I)
- Cópia / Portabilidade (Art.18 II)
- Anonimização/Eliminação de dados antigos de Sessão (Art.18 IV)
- Revogação de Consentimentos de Log/Cookies Tracking

Protocolos atendidos via contato eletrônico no painel administrativo de governança: <?= htmlspecialchars($dpoEmail) ?>. Prazos previstos pela ANPD: Menos de 15 dias para requisições unificadas e simples.</div>
                                    </div>
                                </details>
                                
                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-trash text-rose-400"></i> Política de Retenção de Dados</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-48 overflow-y-auto custom-scrollbar">
                                        <h5 class="text-white font-bold mb-2">Prazos de Retenção (Art.15-16 LGPD)</h5>
                                        <ul class="list-disc pl-4 space-y-2 mb-4">
                                            <li><strong class="text-rose-300">Foto / Checagem:</strong> Exclusão Imediata (&lt;1s). Nenhuma persistência em disco. Base Legal: Art.15 I (Finalidade alcançada).</li>
                                            <li><strong class="text-rose-300">Idade Calculada:</strong> Não armazenada. Convertida em Hash de Sessão Temporário. Base: Art.6 III (Minimização).</li>
                                            <li><strong class="text-rose-300">Logs de Segurança e WAF:</strong> Mantidos por 12 meses. Base Legal: Marco Civil de Internet Art.15 e LGPD Art.15 II.</li>
                                        </ul>
                                    </div>
                                </details>

                                <details class="group bg-slate-900 border border-slate-700 rounded-xl overflow-hidden text-sm">
                                    <summary class="p-3 font-bold text-slate-300 cursor-pointer hover:bg-slate-800 transition flex items-center justify-between">
                                        <span class="flex items-center gap-2"><i class="ph-fill ph-warning-circle text-amber-500"></i> Plano de Resposta a Incidentes</span>
                                        <i class="ph-bold ph-caret-down text-slate-500 group-open:rotate-180 transition"></i>
                                    </summary>
                                    <div class="p-4 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-400 h-48 overflow-y-auto custom-scrollbar">
                                        <h5 class="text-white font-bold mb-2">Fluxo de Incidente (Art.48 LGPD)</h5>
                                        <p class="mb-2">Documento exigido para demonstração de responsabilidade técnica.</p>
                                        <ol class="list-decimal pl-4 space-y-2">
                                            <li><strong class="text-amber-300">Detecção Remota (Imediata):</strong> Acionamento de Gatilhos WAF Front18 contra tentativas de intrusão no site/DDoS.</li>
                                            <li><strong class="text-amber-300">Contenção de Emergência (T+15m):</strong> Acionamento manual da "Catraca Nível 3 (Zero Trust Mode)" via Painel SaaS.</li>
                                            <li><strong class="text-amber-300">Notificação Tática (T+60m):</strong> O DPO titular (<?= htmlspecialchars($dpoEmail) ?>) e o Jurídico serão acionados via sistema com extrato de Logs filtrado para oficiar ANPD caso necessário e dependendo da escala.</li>
                                        </ol>
                                    </div>
                                </details>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 1: CONSENTIMENTO E BANNER (Formulário) -->
                    <div id="dpo-panel-consent" class="dpo-panel block">
                        <div class="">
                            <form id="frmPrivacy" class="space-y-6">
                                
                                <!-- BANNERS E CONTRATO -->
                            <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                                <h4 class="font-bold text-white mb-6 flex items-center gap-2"><i class="ph-bold ph-text-align-left text-emerald-400"></i> Engenharia do Banner de Consentimento</h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Título do Banner</label>
                                        <input type="text" name="banner_title" value="<?= htmlspecialchars($bannerTitle) ?>" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-sm text-white w-full focus:border-emerald-500 focus:outline-none placeholder-slate-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Redação Jurídica (Finalidade)</label>
                                        <textarea name="banner_text" rows="3" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-sm text-slate-300 w-full focus:border-emerald-500 focus:outline-none custom-scrollbar uppercase-first"><?= htmlspecialchars($bannerText) ?></textarea>
                                        <p class="text-[9px] text-slate-500 mt-1 uppercase tracking-wider">Descreva explicitamente por que os dados são coletados (Adequação ANPD).</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 pt-2">
                                        <div>
                                            <label class="block text-xs font-bold text-emerald-500 uppercase tracking-widest mb-1">Ação de Consentimento (Positivo)</label>
                                            <input type="text" name="btn_accept" value="<?= htmlspecialchars($btnAccept) ?>" class="bg-emerald-900/20 border border-emerald-500/50 rounded-lg px-4 py-2 text-sm text-emerald-400 w-full focus:border-emerald-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-rose-500 uppercase tracking-widest mb-1">Ação de Revogação (Negativo)</label>
                                            <input type="text" name="btn_reject" value="<?= htmlspecialchars($btnReject) ?>" class="bg-slate-900 border border-rose-900/50 rounded-lg px-4 py-2 text-sm text-slate-400 w-full focus:border-rose-500 focus:outline-none">
                                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                                <input type="checkbox" name="allow_reject" value="1" <?= isset($privConf['allow_reject']) && $privConf['allow_reject'] ? 'checked' : '' ?> class="rounded bg-slate-800 border-slate-700 text-rose-500">
                                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Botão Visível?</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- NOMINAÇÃO DE DPO -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                                    <h4 class="font-bold text-white mb-4 flex items-center gap-2"><i class="ph-bold ph-identification-badge text-teal-400"></i> Oficial de Dados (DPO)</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email de Contato de Privacidade</label>
                                            <input type="email" name="dpo_email" placeholder="dpo@empresa.com" value="<?= htmlspecialchars($dpoEmail) ?>" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-xs text-white w-full focus:border-teal-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nomenclatura (Label do Botão)</label>
                                            <input type="text" name="dpo_title" value="<?= htmlspecialchars($dpoTitle) ?>" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-xs text-slate-300 w-full focus:border-teal-500 focus:outline-none">
                                        </div>
                                        <div class="pt-2 border-t border-slate-800">
                                            <label class="block text-[10px] font-bold text-teal-500 uppercase tracking-widest mb-1 flex items-center gap-1"><i class="ph-fill ph-lock-key"></i> Passkey do Painel Jurídico</label>
                                            <input type="text" name="dpo_master_key" placeholder="Ex: master-rh-2026" value="<?= htmlspecialchars($privConf['dpo_master_key'] ?? 'front18-master') ?>" class="bg-slate-950 border border-teal-500/50 rounded-lg px-4 py-2 text-xs text-teal-400 w-full shadow-[inset_0_2px_10px_rgba(0,0,0,0.5)] focus:border-teal-400 focus:outline-none placeholder-teal-800/50">
                                            <p class="text-[9px] text-slate-500 mt-1">A senha mestra necessária para descriptografar denúncias no Hub DPO.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="glass-panel p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                                    <div>
                                        <h4 class="font-bold text-white mb-2 flex items-center gap-2"><i class="ph-bold ph-activity text-rose-400"></i> Gestão de Monitoramento</h4>
                                        <p class="text-[10px] text-slate-400 mb-4 leading-relaxed">Habilite rastreio por consentimento no banner flutuante.</p>
                                        
                                        <div class="space-y-3">
                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-google-logo text-emerald-400"></i> Analytics (GA4)</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_analytics" value="1" <?= isset($privConf['has_analytics']) && $privConf['has_analytics'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-emerald-500 transition-colors"></div>
                                              </div>
                                            </label>

                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-currency-dollar text-amber-400"></i> Módulo AdSense</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_marketing" value="1" <?= isset($privConf['has_marketing']) && $privConf['has_marketing'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-amber-500 transition-colors"></div>
                                              </div>
                                            </label>

                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-facebook-logo text-blue-500"></i> Meta Pixel (Ads)</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_meta" value="1" <?= isset($privConf['has_meta']) && $privConf['has_meta'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-blue-500 transition-colors"></div>
                                              </div>
                                            </label>

                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-tiktok-logo text-pink-500"></i> TikTok Pixel</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_tiktok" value="1" <?= isset($privConf['has_tiktok']) && $privConf['has_tiktok'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-pink-500 transition-colors"></div>
                                              </div>
                                            </label>

                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-fire text-orange-500"></i> Heatmaps (Hotjar)</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_heatmaps" value="1" <?= isset($privConf['has_heatmaps']) && $privConf['has_heatmaps'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-orange-500 transition-colors"></div>
                                              </div>
                                            </label>

                                            <label class="flex items-center justify-between group cursor-pointer">
                                              <span class="text-xs text-slate-300 font-bold tracking-widest uppercase flex items-center gap-2"><i class="ph-bold ph-webhooks-logo text-indigo-400"></i> Zapier Webhooks</span>
                                              <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="has_webhooks" value="1" <?= isset($privConf['has_webhooks']) && $privConf['has_webhooks'] ? 'checked' : '' ?> class="sr-only peer">
                                                <div class="w-9 h-5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 peer-checked:bg-indigo-500 transition-colors"></div>
                                              </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="button" onclick="syncPrivacyConfig(this, this.closest('form'))" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-4 rounded-xl font-black text-sm shadow-[0_4px_15px_rgba(16,185,129,0.2)] hover:shadow-[0_4px_20px_rgba(16,185,129,0.4)] transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                                    <i class="ph-bold ph-gavel text-lg"></i> <span>Atermar Diretrizes LGPD & Webhook</span>
                                </button>
                            </div>
                        </form>
                            </form>
                        </div>
                    </div> <!-- FECHA TAB 1 -->
                </div> <!-- FECHA DPO PANELS WRAPPER -->

                <!-- SCRIPT DAS ABAS E SALVAMENTO -->
                <script>
                function switchDpoTab(tabId) {
                    // Oculta todas
                    document.querySelectorAll('.dpo-panel').forEach(el => {
                        el.classList.remove('block');
                        el.classList.add('hidden');
                    });
                    
                    // Reseta botões
                    document.querySelectorAll('.dpo-tab-btn').forEach(el => {
                        el.classList.remove('bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
                        el.classList.add('bg-slate-900', 'text-slate-400', 'border-slate-800');
                    });

                    // Ativa alvo
                    document.getElementById('dpo-panel-' + tabId).classList.remove('hidden');
                    document.getElementById('dpo-panel-' + tabId).classList.add('block');
                    
                    let btn = document.getElementById('btn-dpo-' + tabId);
                    btn.classList.remove('bg-slate-900', 'text-slate-400', 'border-slate-800');
                    btn.classList.add('bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
                }

                function syncPrivacyConfig(btn, form) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-lg"></i> <span>Submetendo Termos...</span>';
                    btn.classList.add('opacity-80', 'cursor-not-allowed');
                    btn.disabled = true;
                    
                    const formData = new FormData(form);
                    formData.append('action', 'save_privacy');
                    
                    fetch('?route=dashboard', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            btn.innerHTML = '<i class="ph-bold ph-check-circle text-lg"></i> <span>Webhook DPO Concluído!</span>';
                            btn.classList.replace('bg-emerald-600', 'bg-teal-600');
                        } else {
                            btn.innerHTML = 'Erro ao Salvar!';
                            btn.classList.add('bg-rose-600');
                        }
                        
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.classList.remove('opacity-80', 'cursor-not-allowed', 'bg-teal-600', 'bg-rose-600');
                            btn.disabled = false;
                        }, 3000);
                    })
                    .catch(e => {
                        FrontToast.show('error', "ERRO LOCAL: " + e.message);
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('opacity-80', 'cursor-not-allowed');
                        btn.disabled = false;
                    });
                }

                function unlockDpoEngine(e, form) {
                    e.preventDefault();
                    const btn = form.querySelector('button');
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
                    
                    const fd = new FormData(form);
                    fd.append('action', 'dpo_unlock');
                    
                    fetch('?route=dashboard', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) location.reload();
                        else {
                            FrontToast.show('error', d.error);
                            btn.innerHTML = original;
                        }
                    });
                }

                function lockDpoEngine() {
                    if(!confirm('Deseja criptografar e trancar sua sessão DPO? Isso impedirá o acesso aos dados abertos dos denunciantes.')) return;
                    const fd = new FormData();
                    fd.append('action', 'dpo_lock');
                    fetch('?route=dashboard', { method: 'POST', body: fd })
                    .then(() => location.reload());
                }

                function resolveDpoTicket(e, form) {
                    e.preventDefault();
                    if(!confirm('Confirmar o encerramento jurídico deste ticket? Processo destrutivo, sem retorno.')) return;
                    
                    const btn = form.querySelector('button');
                    const original = btn.innerHTML;
                    btn.innerHTML = 'Processando...';
                    
                    const fd = new FormData(form);
                    fd.append('action', 'dpo_resolve');
                    
                    fetch('?route=dashboard', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) location.reload();
                        else { FrontToast.show('error', d.error); btn.innerHTML = original; }
                    });
                }
                </script>

                <div id="dpo-panel-reports" class="dpo-panel hidden">
                <!-- CAIXA DE ENTRADA DO DPO SECURE -->
                <?php
                $isDpoUnlocked = isset($_SESSION['dpo_unlocked_' . $userId]);
                if ($isDpoUnlocked && isset($domain['id'])) {
                    $stmtDpo = $pdo->prepare("SELECT * FROM saas_dpo_reports WHERE domain_id = ? ORDER BY created_at DESC LIMIT 50");
                    $stmtDpo->execute([$domain['id']]);
                    $dpoReports = $stmtDpo->fetchAll();
                } else {
                    $dpoReports = [];
                }
                ?>
                <div class="glass-panel p-8 rounded-2xl md:mt-8 border border-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                        <h4 class="text-xl font-bold text-white flex items-center gap-2"><i class="ph-bold ph-envelope-simple-open text-teal-500"></i> Caixa de Entrada DPO</h4>
                        <?php if($isDpoUnlocked): ?>
                            <button onclick="lockDpoEngine()" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-1.5 rounded text-xs font-bold uppercase tracking-widest transition flex items-center gap-1.5"><i class="ph-bold ph-lock-key"></i> Trancar Acesso</button>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-400 mb-6">Listagem das solicitações formais enviadas pelos usuários perante as leis locais de Privacidade (Acesso a dados, deleção, denúncia de vazamentos, etc).</p>
                    
                    <?php if(!$isDpoUnlocked): ?>
                    <div class="bg-slate-900/50 p-8 rounded-xl border border-slate-800 flex flex-col items-center text-center">
                        <i class="ph-bold ph-lock-key text-5xl text-slate-600 mb-4"></i>
                        <h5 class="text-lg font-bold text-white mb-2">Acesso Classificado</h5>
                        <p class="text-sm text-slate-400 max-w-sm mb-6">Insira a Passkey Mestra definida pelo DPO Corporativo para descriptografar os tickets judiciais desta sessão.</p>
                        
                        <form id="frmDpoUnlock" onsubmit="unlockDpoEngine(event, this)" class="w-full max-w-sm flex gap-3">
                            <input type="password" name="dpo_key" required placeholder="Insira a Passkey Mestra..." class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-sm text-white focus:border-teal-500 focus:outline-none placeholder-slate-600">
                            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition flex items-center text-lg"><i class="ph-bold ph-key"></i></button>
                        </form>
                    </div>
                    <?php else: ?>
                    
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left bg-slate-900/50">
                            <thead class="bg-slate-800 border-b border-slate-700">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Protocolo / Data</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Requisitante</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Solicitação Confidencial</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php if (empty($dpoReports)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">Nenhum protocolo registrado até o momento.</td>
                                </tr>
                                <?php else: foreach($dpoReports as $rpt): ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-white font-mono text-sm">#F18-<?= str_pad($rpt['id'], 5, '0', STR_PAD_LEFT) ?></div>
                                        <div class="text-xs text-slate-500 mt-1"><?= date('d/m/Y H:i', strtotime($rpt['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-teal-400"><?= htmlspecialchars($rpt['reporter_name']) ?></div>
                                        <div class="text-xs text-slate-400 mt-1"><?= htmlspecialchars($rpt['reporter_email']) ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($rpt['reporter_phone']) ?></div>
                                        <?php if(!empty($rpt['reporter_role'])): ?>
                                        <div class="text-[10px] uppercase font-bold text-emerald-500 mt-1 bg-emerald-900/30 inline-block px-1.5 py-0.5 rounded"><?= htmlspecialchars($rpt['reporter_role']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if(!empty($rpt['violation_type'])): ?>
                                            <div class="text-xs font-bold text-red-400 mb-1 border-b border-red-900/30 pb-1 flex items-center gap-1"><i class="ph-bold ph-warning"></i> <?= htmlspecialchars($rpt['violation_type']) ?></div>
                                        <?php endif; ?>
                                        <?php if(!empty($rpt['content_url'])): ?>
                                            <div class="text-xs text-blue-400 mb-2 truncate max-w-sm"><a href="<?= htmlspecialchars($rpt['content_url'] ?? '') ?>" target="_blank" class="hover:underline"><?= htmlspecialchars($rpt['content_url'] ?? '') ?></a></div>
                                        <?php endif; ?>
                                        <div class="text-sm text-slate-300 bg-slate-950 p-3 rounded border border-slate-800 max-h-32 overflow-y-auto w-full max-w-sm leading-relaxed">
                                            <?= nl2br(htmlspecialchars($rpt['report_message'] ?? '')) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                                        <?php if($rpt['status'] === 'pending'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded bg-yellow-500/10 border border-yellow-500 text-yellow-500 text-[10px] font-bold uppercase tracking-widest mb-3">Em Análise</span>
                                            
                                            <form class="mt-2 text-left w-64 ml-auto" onsubmit="resolveDpoTicket(event, this)">
                                                <input type="hidden" name="report_id" value="<?= $rpt['id'] ?>">
                                                <textarea name="resolution_notes" required rows="2" class="w-full bg-slate-900 border border-slate-700 p-2 text-xs text-slate-300 rounded focus:border-teal-500 focus:outline-none mb-2" placeholder="Notas de Resolução e Ajustes..."></textarea>
                                                <button type="submit" class="w-full bg-slate-800 hover:bg-teal-600 border border-teal-500/20 text-white py-2 px-3 rounded text-[10px] uppercase font-bold tracking-widest transition flex items-center justify-center gap-1"><i class="ph-bold ph-check"></i> Encerrar Protocolo</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded bg-teal-500/10 border border-teal-500 text-teal-400 text-[10px] font-bold uppercase tracking-widest mb-3">Resolvido</span>
                                            <div class="text-left w-64 ml-auto">
                                                <div class="text-[9px] text-slate-500 mb-1">Encerrado em: <?= date('d/m/Y H:i', strtotime($rpt['resolved_at'])) ?></div>
                                                <div class="text-[10px] text-slate-400 bg-slate-800/50 p-2 rounded italic mt-1 whitespace-normal break-words border border-slate-700/50">
                                                    <?= nl2br(htmlspecialchars($rpt['report_notes'] ?? '')) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                </div> <!-- FECHA TAB 4 REPORTS -->
            </div>

            <!-- ====== TAB 6: SUSPICIOUS (Medo/Valor) ====== -->
            <div id="tab-suspicious" class="tab-content max-w-5xl mx-auto">
                <div class="glass-panel p-8 rounded-2xl relative overflow-hidden text-center mb-8 border-orange-500/20">
                    <div class="absolute inset-0 bg-gradient-to-t from-orange-500/5 to-transparent pointer-events-none"></div>
                    <i class="ph-fill ph-warning-octagon text-5xl text-orange-500 mb-4 inline-block drop-shadow-lg"></i>
                    <h2 class="text-2xl font-bold text-white mb-2">Monitoramento de Vetores de Risco</h2>
                    <p class="text-sm text-slate-400 max-w-xl mx-auto">Esta tela lista anomalias sistêmicas (menores tentando burlar scripts de forma massiva via DevTools ou robôs/scraping automatizados). O Front18 bloqueou esses hits ativamente.</p>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex items-center justify-between border-l-4 border-l-red-500">
                        <div>
                            <p class="text-xs font-mono text-slate-500 mb-1">11/03/2026 18:22 UTC - IP 185.22.***.***</p>
                            <h4 class="font-bold text-sm text-white">Spike de Requisições F12 Bypass</h4>
                            <p class="text-xs text-slate-400 max-w-3xl mt-1">O usuário detectado tentou desativar a DOM Mask 14 vezes consecutivas. Hit interrompido sem expor arquivo raw.</p>
                        </div>
                        <span class="bg-red-500/10 text-red-500 px-3 py-1 text-xs font-bold rounded">Mitigado Autom.</span>
                    </div>
                </div>
            </div>

            <!-- ====== TAB 7: BILLING (Assinatura) ====== -->
            <div id="tab-billing" class="tab-content max-w-3xl mx-auto">
                 <div class="glass-panel p-8 rounded-3xl border border-primary-500">
                    <span class="bg-primary-500/20 text-primary-400 font-bold uppercase tracking-widest text-[10px] px-3 py-1 rounded inline-block mb-4 border border-primary-500/20">Plano Ativo</span>
                    <h2 class="text-4xl font-black text-white mb-1"><?= htmlspecialchars($currentPlanName) ?> <span class="text-2xl font-normal text-slate-500">/ B2B</span></h2>
                    <p class="text-sm text-slate-400 mb-8 border-b border-slate-800 pb-8">Renovação ciclo atual: final do mês vigente. Acesso à Laudos e métricas avançadas.</p>
                    
                    <div class="space-y-4 mb-8">
                        <div>
                            <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                <span>Quota de Dossiês (Acessos Processados)</span>
                                <span class="<?= $textColor ?>"><?= number_format($totalAcessos, 0, ',', '.') ?> / <?= number_format($maxRequestsAllowed, 0, ',', '.') ?></span>
                            </div>
                            <div class="w-full bg-slate-900 rounded-full h-2 border border-slate-800 relative overflow-hidden">
                                <div class="<?= $usageColor ?> h-2 rounded-full absolute left-0 top-0 transition-all duration-500" style="width: <?= $usagePercent ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <button onclick="FrontToast.show('info', 'Nenhuma fatura anterior localizada.')" class="bg-transparent hover:bg-white/5 text-slate-400 font-bold px-6 py-3 rounded-xl transition-colors text-sm border border-slate-800">Ver Faturas Anteriores</button>
                    </div>

                    <?php
                    $stmtFetchPlans = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
                    $allAssinaturas = $stmtFetchPlans->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="mt-12 pt-10 border-t border-slate-800">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="ph-fill ph-rocket-launch text-primary-500"></i> Opções de Assinatura (Upgrade/Downgrade)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach($allAssinaturas as $ass): 
                                $isActivePlan = ($planDetails['id'] == $ass['id']);
                                $assPrice = number_format($ass['price'], 2, ',', '.');
                            ?>
                            <div class="bg-slate-950 border <?= $isActivePlan ? 'border-primary-500 shadow-[0_0_30px_rgba(99,102,241,0.15)] bg-slate-900/50' : 'border-slate-800 hover:border-slate-700' ?> rounded-2xl p-8 relative flex flex-col transition-all">
                                <?php if($isActivePlan): ?>
                                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-[10px] font-bold px-4 py-1.5 uppercase tracking-widest rounded-full border border-primary-400 shadow-lg glow-effect whitespace-nowrap">Seu Plano Atual</div>
                                <?php endif; ?>
                                <h4 class="text-white font-bold text-xl mb-1"><?= htmlspecialchars($ass['name']) ?></h4>
                                <p class="text-primary-400 font-black text-3xl mb-6">R$ <?= $assPrice ?> <span class="text-xs text-slate-500 font-normal">/ mês</span></p>
                                <ul class="text-sm text-slate-400 space-y-3 mb-8 flex-1">
                                    <li class="flex items-center gap-3"><i class="ph-bold ph-check text-emerald-500"></i> <strong class="text-white"><?= number_format($ass['max_requests_per_month'], 0, ',', '.') ?></strong> Processamentos B2B</li>
                                    <li class="flex items-center gap-3"><i class="ph-bold ph-check text-emerald-500"></i> <strong class="text-white"><?= $ass['max_domains'] ?></strong> Site(s) Blindado(s)</li>
                                    <li class="flex items-center gap-3"><i class="<?= $ass['has_seo_safe'] ? 'ph-bold ph-check text-emerald-500' : 'ph-bold ph-x text-red-500' ?>"></i> Acesso Bot de Buscadores (Google)</li>
                                    <li class="flex items-center gap-3"><i class="<?= $ass['has_anti_scraping'] ? 'ph-bold ph-check text-emerald-500' : 'ph-bold ph-x text-red-500' ?>"></i> WAF Anti-Scraping Blindado</li>
                                    <li class="flex items-center gap-3"><i class="ph-bold ph-check text-emerald-500"></i> Dossiê LGPD / Blockchain Level <?= $ass['allowed_level'] ?></li>
                                </ul>
                                <button onclick="FrontToast.show('warning', 'Integração de Webhook Financeiro pendente. Chame o suporte técnico (B20) para emissão do contrato PIX/Cartão.')" class="w-full py-4 rounded-xl font-bold text-sm tracking-wide transition-all <?= $isActivePlan ? 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700' : 'bg-primary-600 hover:bg-primary-500 text-white shadow-[0_4px_15px_rgba(99,102,241,0.25)] hover:shadow-[0_4px_25px_rgba(99,102,241,0.4)]' ?>" <?= $isActivePlan ? 'disabled' : '' ?>>
                                    <?php 
                                        if ($isActivePlan) {
                                            echo 'Assinatura Ativa';
                                        } else {
                                            echo ($ass['price'] > $planDetails['price']) ? 'Fazer Upgrade de Conta' : 'Fazer Downgrade';
                                        }
                                    ?>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== TAB 8: API (Snippet) ====== -->
            <div id="tab-api" class="tab-content max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-white mb-6">Integração do Domínio</h2>
                
                <div class="glass-panel p-6 mb-6 rounded-2xl border border-dashed border-slate-700">
                    <h3 class="text-sm font-bold text-white mb-2">Comportamento Seguro de Queda (Deny URL)</h3>
                    <p class="text-[10px] text-slate-400 mb-4">Caso não deseje a tela padrão (Front18 Safe Exit), preencha o link HTTPS para reter o lead ou devolvê-lo com segurança para um domínio limpo de conversão.</p>
                    <form method="POST" action="" class="flex gap-2">
                        <input type="hidden" name="action" value="save_fallback">
                        <input type="text" name="deny_url" placeholder="Ex: https://seudominio.com/versao-livre" value="<?= htmlspecialchars($config['deny_url'] ?? '') ?>" class="bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-sm text-slate-300 w-full focus:border-primary-500 focus:outline-none">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white font-bold px-4 py-2 rounded-lg text-sm whitespace-nowrap transition-colors">Salvar Rota</button>
                    </form>
                </div>
                <div class="glass-panel p-6 mb-6 rounded-2xl">
                    <label class="block text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Authorization Secret (Chave B2B)</label>
                    <div class="flex items-center gap-0">
                        <input type="text" readonly value="<?= htmlspecialchars($apiKey) ?>" class="bg-slate-950 border border-slate-700/50 rounded-l-xl px-4 py-3 text-amber-400 font-mono text-sm w-full focus:outline-none tracking-widest bg-stripes">
                        <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($apiKey) ?>'); FrontToast.show('success', 'Chave Mestra Copiada com sucesso!');" class="bg-slate-800 hover:bg-slate-700 border border-slate-700/50 border-l-0 rounded-r-xl px-5 py-3 text-white transition-colors flex items-center gap-2 font-medium shrink-0"><i class="ph-bold ph-copy"></i> Copiar</button>
                    </div>
                </div>
                
                <div class="glass-panel p-0 rounded-2xl overflow-hidden shadow-inner border border-white/5 bg-[#0b1120] mb-6">
                    <div class="px-4 py-2 border-b border-white/5 bg-slate-900/50 flex justify-between items-center">
                        <p class="text-[10px] font-mono text-slate-400">&lt;head&gt; script injetável</p>
                    </div>
                    <pre class="p-5 font-mono text-xs leading-relaxed text-slate-300 overflow-x-auto selection:bg-primary-500/30">
<span class="text-slate-500">&lt;!-- Front18 Pro: Proteção Ativa B2B SDK --&gt;</span>
<span class="text-blue-400">&lt;script</span> <span class="text-green-300">src</span><span class="text-white">=</span><span class="text-amber-300">"https://SEUSAAS.com.br/public/sdk/front18.js"</span>
        <span class="text-green-300">data-auto-init</span><span class="text-white">=</span><span class="text-amber-300">"true"</span>
        <span class="text-green-300">data-api-key</span><span class="text-white">=</span><span class="text-amber-300">"<?= htmlspecialchars($apiKey) ?>"</span>
        <span class="text-green-300">data-terms-version</span><span class="text-white">=</span><span class="text-amber-300">"v1.0"</span><?php if(!empty($config['deny_url'])): ?> 
        <span class="text-green-300">data-deny-url</span><span class="text-white">=</span><span class="text-amber-300">"<?= htmlspecialchars($config['deny_url']) ?>"</span><?php endif; ?> 
        <span class="text-blue-400">defer&gt;&lt;/script&gt;</span></pre>
                </div>
                
                <h3 class="text-lg font-bold text-white mb-4">Controle Estrutural Server-Side</h3>
                <div class="glass-panel p-6 rounded-2xl mb-6 border-l-[3px] border-l-primary-500">
                    <p class="text-sm text-slate-300 mb-4">Para blindar arquivos críticos (mídias +18), envolva as imagens ou vídeos em div's identificadas. Nossa API só liberará o conteúdo em tela se o Session_ID tiver consentido.</p>
                    
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 font-mono text-[11px] text-slate-400">
<span class="text-slate-500">&lt;!-- Exemplo de Mídia Privada --&gt;</span><br>
<span class="text-pink-400">&lt;div</span> <span class="text-green-300">data-front18</span><span class="text-white">=</span><span class="text-amber-300">"locked"</span> <span class="text-green-300">data-id</span><span class="text-white">=</span><span class="text-amber-300">"v12-cena-final"</span><span class="text-pink-400">&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-slate-500">&lt;!-- Sua img/video aqui --&gt;</span><br>
<span class="text-pink-400">&lt;/div&gt;</span>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-slate-800">
                    <a href="?route=docs" class="inline-flex items-center gap-2 text-primary-400 hover:text-white transition-colors text-sm font-bold">
                        <i class="ph-bold ph-book-open"></i> Acessar Portal de Ajuda Completo
                    </a>
                </div>
            </div>

            <!-- ====== TAB WP: GERENCIAMENTO REMOTO WORDPRESS ====== -->
            <div id="tab-wp" class="tab-content max-w-4xl mx-auto">
                <div class="glass-panel p-8 rounded-2xl relative overflow-hidden mb-8 border border-blue-500/20">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 pointer-events-none"></div>
                    <div class="flex items-start gap-6 relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/20">
                            <i class="ph-bold ph-plugs-connected text-3xl text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-2 tracking-tight">Sincronização Remota: WordPress</h3>
                            <p class="text-sm text-slate-400 max-w-2xl leading-relaxed">Você está usando o nosso Plugin WordPress no seu site? Configure aqui quais páginas do seu WP serão protegidas e nós enviaremos o comando para o seu site via Webhook invisível no modo "Thin Client". O seu plugin WP atuará apenas como um receptor burro obedecendo nossa inteligência de ponta.</p>
                        </div>
                    </div>
                </div>

                <?php
                $wpConf = !empty($config['wp_rules']) ? json_decode($config['wp_rules'], true) : [];
                $wpUrl = $config['wp_url'] ?? '';
                $wpGlobal = isset($wpConf['global']) ? $wpConf['global'] : false;
                $wpHome = isset($wpConf['home']) ? $wpConf['home'] : false;
                $wpCpts = isset($wpConf['cpts']) ? $wpConf['cpts'] : [];
                ?>
                <form id="frmWpSync" class="space-y-6" onsubmit="event.preventDefault(); syncWpConfig(this);">
                    <!-- Detalhes do Endpoint -->
                    <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                        <h4 class="font-bold text-white mb-6 flex items-center gap-2"><i class="ph-bold ph-globe text-blue-400"></i> Endpoint do seu Site</h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">URL Completa da Instalação do WordPress</label>
                            <input type="url" name="wp_url" placeholder="https://seudominio.com.br" value="<?= htmlspecialchars($wpUrl) ?>" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all font-mono">
                            <p class="text-[10px] text-slate-500 mt-2">Dica: Se o seu WordPress foi instalado em uma subpasta (ex: /blog), coloque a URL exata da subpasta. Usaremos isso para localizar a Rest API.</p>
                        </div>
                    </div>

                    <!-- Controle de Escopo -->
                    <?php if (($config['display_mode'] ?? 'global_lock') === 'blur_media'): ?>
                    <div class="glass-panel p-6 rounded-2xl border border-emerald-500/30 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/10 to-transparent pointer-events-none"></div>
                        <h4 class="font-bold text-emerald-400 mb-2 flex items-center gap-2"><i class="ph-fill ph-check-circle text-xl"></i> Escopo Automático Ativado (Modo Blur)</h4>
                        <p class="text-sm text-slate-300 leading-relaxed mb-4">Seu portal está utilizando o módulo <strong>Media Teaser (Blur Dinâmico)</strong>. Neste modo, não é necessário mapear páginas ou categorias. O motor Javascript do Front18 vasculha automaticamente qualquer rota do seu WordPress para caçar as imagens selecionadas na <strong>Matriz Granular</strong>.</p>
                        <p class="text-xs text-slate-500 italic">O escudo será injetado automaticamente pelo plugin WP em todas indexações, blindando inclusive sidebars e footers.</p>
                        <!-- Inputs ocultos para manter compatibilidade e o form salvar sem erros -->
                        <input type="hidden" name="wp_home" value="1">
                        <input type="hidden" name="wp_cpts[]" value="post">
                        <input type="hidden" name="wp_cpts[]" value="page">
                    </div>
                    <?php else: ?>
                    <div class="glass-panel p-6 rounded-2xl border border-slate-800">
                        <h4 class="font-bold text-white mb-6 flex items-center gap-2"><i class="ph-bold ph-crosshair text-indigo-400"></i> Onde o WAF Tela Preta será ativado</h4>
                        <p class="text-xs text-slate-400 leading-relaxed mb-6 pb-4 border-b border-slate-800/50">Você está rodando sob a Blindagem por Modal Intransponível (Tapa Tudo). Marque abaixo quais terrenos de seu domínio deverão cobrar o passaporte. Outras páginas não marcadas passarão livres.</p>
                        
                        <div class="space-y-4">
                            <!-- Global -->
                            <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border <?= $wpGlobal ? 'border-blue-500/50 bg-blue-900/10' : 'border-slate-800' ?> transition hover:border-slate-700">
                                <div>
                                    <h5 class="font-bold text-sm text-slate-200">Bloquear o Site Inteiro</h5>
                                    <p class="text-[10px] text-slate-500 mt-1">Se ativo, qualquer rota ou query de post passará pelo WAF de idade do Front18. Ideal se 100% de seu conteúdo é adulto.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" name="wp_global" value="1" <?= $wpGlobal ? 'checked' : '' ?> onchange="this.closest('div.border').classList.toggle('border-blue-500/50'); this.closest('div.border').classList.toggle('bg-blue-900/10'); document.getElementById('granular_scope').classList.toggle('opacity-50'); document.getElementById('granular_scope').classList.toggle('pointer-events-none');" class="sr-only peer">
                                  <div class="w-11 h-6 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 peer-checked:bg-blue-500 transition-colors"></div>
                                </label>
                            </div>

                            <div id="granular_scope" class="space-y-4 <?= $wpGlobal ? 'opacity-50 pointer-events-none' : '' ?> transition-opacity">
                                <!-- Home -->
                                <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800 transition hover:border-slate-700">
                                    <div>
                                        <h5 class="font-bold text-sm text-slate-200">Trancar Apenas a Página Inicial (Root)</h5>
                                        <p class="text-[10px] text-slate-500 mt-1">O portal de entrada ficará suprimido pelo Modal. Links diretos para posts passarão livre.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" name="wp_home" value="1" <?= $wpHome ? 'checked' : '' ?> class="sr-only peer">
                                      <div class="w-11 h-6 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 peer-checked:bg-indigo-500 transition-colors"></div>
                                    </label>
                                </div>

                                <!-- CPTs -->
                                <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 transition hover:border-slate-700">
                                    <h5 class="font-bold text-sm text-slate-200 mb-3">Trancar Tipos de Postagens Selecionados</h5>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                            <input type="checkbox" name="wp_cpts[]" value="post" <?= in_array('post', $wpCpts) ? 'checked' : '' ?> class="rounded bg-slate-900 border-slate-700 text-blue-500">
                                            Posts (Artigos)
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                            <input type="checkbox" name="wp_cpts[]" value="page" <?= in_array('page', $wpCpts) ? 'checked' : '' ?> class="rounded bg-slate-900 border-slate-700 text-blue-500">
                                            Páginas
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                            <input type="checkbox" name="wp_cpts[]" value="product" <?= in_array('product', $wpCpts) ? 'checked' : '' ?> class="rounded bg-slate-900 border-slate-700 text-blue-500">
                                            Produtos (WooCommerce)
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                            <input type="checkbox" name="wp_cpts[]" value="portfolio" <?= in_array('portfolio', $wpCpts) ? 'checked' : '' ?> class="rounded bg-slate-900 border-slate-700 text-blue-500">
                                            Portfolio
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="pt-6 border-t border-slate-800 mt-6 flex justify-end">
                        <button type="submit" id="btnSaveWp" class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-[0_4px_15px_rgba(59,130,246,0.2)] hover:shadow-[0_4px_20px_rgba(59,130,246,0.4)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                            <i class="ph-bold ph-paper-plane-right text-lg"></i> <span>Salvar & Fazer Push no WP Plugin</span>
                        </button>
                    </div>

                    <!-- Mensagem de Resposta -->
                    <div id="wp_sync_response" class="hidden rounded-xl p-4 mt-4 font-mono text-xs flex items-center justify-between"></div>

                    <script>
                    function syncWpConfig(form) {
                        const btn = document.getElementById('btnSaveWp');
                        const responseBox = document.getElementById('wp_sync_response');
                        
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-lg"></i> <span>Comunicando com seu Servidor...</span>';
                        btn.classList.add('opacity-80', 'cursor-not-allowed');
                        btn.disabled = true;
                        responseBox.classList.add('hidden');
                        
                        const formData = new FormData(form);
                        formData.append('action', 'save_wp');
                        
                        fetch('?route=dashboard', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            btn.classList.remove('bg-blue-600', 'hover:bg-blue-500', 'opacity-80', 'cursor-not-allowed');
                            btn.disabled = false;
                            
                            responseBox.classList.remove('hidden', 'bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20', 'bg-red-500/10', 'text-red-400', 'border-red-500/20', 'border');
                            
                            if(data.push_status) {
                                btn.innerHTML = '<i class="ph-bold ph-check text-lg"></i> <span>Sincronizado!</span>';
                                btn.classList.add('bg-emerald-600', 'shadow-emerald-500/20');
                                responseBox.classList.add('bg-emerald-500/10', 'text-emerald-400', 'border', 'border-emerald-500/20');
                                responseBox.innerHTML = '<span><i class="ph-fill ph-check-circle"></i> ' + data.push_msg + '</span>';
                            } else {
                                btn.innerHTML = '<i class="ph-bold ph-warning text-lg"></i> <span>Atenção na Redireção</span>';
                                btn.classList.add('bg-amber-600', 'shadow-amber-500/20');
                                responseBox.classList.add('bg-red-500/10', 'text-red-400', 'border', 'border-red-500/20');
                                responseBox.innerHTML = '<span><i class="ph-fill ph-x-circle"></i> ' + data.push_msg + '</span>';
                            }
                            
                            setTimeout(() => {
                                btn.innerHTML = originalHTML;
                                btn.className = "w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-[0_4px_15px_rgba(59,130,246,0.2)] hover:shadow-[0_4px_20px_rgba(59,130,246,0.4)] transition-all flex items-center justify-center gap-2 uppercase tracking-wide";
                            }, 5000);
                        });
                    }
                    </script>
                </form>

                <!-- SECTION: GERENCIADOR GRÃ-MESTRE DE MÍDIA -->
                <div class="mt-12 pt-10 border-t border-slate-800">
                    <div class="glass-panel p-8 rounded-2xl border border-emerald-500/20 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 pointer-events-none"></div>
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 relative">
                            <div>
                                <h4 class="font-bold text-white text-xl flex items-center gap-2 mb-2"><i class="ph-bold ph-images text-emerald-400"></i> Gerenciador Grã-Mestre de Mídia</h4>
                                <p class="text-[11px] text-slate-400 max-w-xl">Blindagem granular extrema. Conecte-se ao WordPress e ative o Blur **individualmente** foto por foto. Ideal para vitrines de e-commerce onde algumas fotos são livres e outras são +18. Isso desabilita as regras globais genéricas de imagens.</p>
                            </div>
                            <button id="btnLoadMedia" type="button" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl shadow-[0_4px_15px_rgba(16,185,129,0.2)] hover:shadow-[0_4px_20px_rgba(16,185,129,0.4)] transition-all flex items-center gap-2 text-sm uppercase tracking-wide shrink-0">
                                <i class="ph-bold ph-plugs-connected"></i> Conectar Biblioteca
                            </button>
                        </div>
                        
                        <!-- Conteiner Dinâmico da Biblioteca -->
                        <div id="mediaManagerWorkspace" class="hidden">
                            <!-- Barra de Ferramentas / Filtros -->
                            <div class="flex flex-col md:flex-row gap-4 mb-6 pt-4 border-t border-slate-800">
                                <div class="flex-1 flex gap-2">
                                    <div class="relative flex-1">
                                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                        <input type="text" id="mediaSearchInput" placeholder="Buscar por nome do arquivo..." class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 transition-colors">
                                    </div>
                                    <div class="relative w-48">
                                        <i class="ph-bold ph-folder absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                        <select id="mediaFolderSelect" class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 appearance-none cursor-pointer">
                                            <option value="all">Todas as Pastas</option>
                                        </select>
                                        <i class="ph-bold ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
                                    </div>
                                    <button type="button" id="btnFilterMedia" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition-colors border border-slate-700">Filtrar</button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-700/50">
                                <p class="text-xs text-slate-400 font-mono" id="mediaStats"><i class="ph-bold ph-circle-notch animate-spin"></i> Analisando Servidor...</p>
                                <button type="button" id="btnSaveMedia" class="relative overflow-hidden bg-slate-900 border border-slate-700 hover:border-emerald-500 text-white font-bold px-4 py-2 rounded-lg text-xs transition-colors flex items-center gap-2">
                                    <i class="ph-bold ph-floppy-disk text-emerald-400"></i> Salvar Matriz de Blindagem
                                </button>
                            </div>
                            
                            <form id="frmMediaSync">
                                <!-- Grid Virtualizada das Imagens -->
                                <div id="mediaGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                    <!-- Populated via Ajax -->
                                </div>
                                <div class="mt-4 flex justify-center hidden" id="mediaPaginationBox">
                                    <button type="button" id="btnLoadMoreMedia" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-6 py-2 rounded-full text-xs uppercase tracking-wider border border-slate-700">
                                        Carregar Mais Imagens...
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <style>
                    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
                    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15,23,42,0.5); border-radius: 10px; }
                    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(51,65,85, 0.8); border-radius: 10px; }
                    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(71,85,105, 1); }
                    .media-checkbox:checked + div { border-color: #10b981; }
                    .media-checkbox:checked + div .overlay-blur { opacity: 1; }
                    .media-checkbox:checked + div .check-icon-media { opacity: 1; transform: scale(1); }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const btnLoad = document.getElementById('btnLoadMedia');
                    const workspace = document.getElementById('mediaManagerWorkspace');
                    const grid = document.getElementById('mediaGrid');
                    const btnMore = document.getElementById('btnLoadMoreMedia');
                    const btnSave = document.getElementById('btnSaveMedia');
                    const stats = document.getElementById('mediaStats');
                    
                    let currentPage = 1;
                    let isLoading = false;
                    let currentlyProtectedIds = null; // Nulo para indicar que precisamos puxar a verdade do backend no 1º load

                    // SISTEMA DE ESTADO GLOBAL: Monitorar mudanças nos inputs da grid independentemente da página
                    grid.addEventListener('change', (e) => {
                        if (e.target && e.target.classList.contains('media-checkbox')) {
                            const id = String(e.target.value);
                            if (e.target.checked) {
                                if (!currentlyProtectedIds.includes(id)) currentlyProtectedIds.push(id);
                            } else {
                                currentlyProtectedIds = currentlyProtectedIds.filter(v => v !== id);
                            }
                        }
                    });

                    const loadMedia = async (page = 1, append = false) => {
                        if(isLoading) return;
                        isLoading = true;
                        
                        const searchVal = document.getElementById('mediaSearchInput').value;
                        const folderVal = document.getElementById('mediaFolderSelect').value;
                        
                        if(!append) {
                            grid.innerHTML = '<div class="col-span-full py-12 text-center text-emerald-500/50 flex flex-col items-center justify-center gap-2"><i class="ph-bold ph-circle-notch animate-spin text-3xl"></i><span class="text-xs uppercase tracking-widest font-bold">Hackeando Servidor...</span></div>';
                            workspace.classList.remove('hidden');
                        } else {
                            btnMore.innerHTML = '<i class="ph-bold ph-circle-notch animate-spin"></i> Puxando...';
                        }

                        try {
                            const res = await fetch(`?route=dashboard&action=load_wp_media&page=${page}&search=${encodeURIComponent(searchVal)}&folder=${encodeURIComponent(folderVal)}`);
                            const data = await res.json();
                            
                            if (data.error) {
                                FrontToast.show('error', "ERRO DE CONEXÃO: " + data.error);
                                if(!append) workspace.classList.add('hidden');
                                isLoading = false;
                                return;
                            }
                            
                            if(!append) {
                                grid.innerHTML = '';
                                // Inicializa a array baseada no backend SOMENTE se ainda for nula
                                // Isso evita apagar o que o usuário marcou localmente caso ele filtre a página
                                if (currentlyProtectedIds === null) {
                                    currentlyProtectedIds = data.protected_ids ? data.protected_ids.map(String) : [];
                                }
                                
                                // Atualiza o Dropdown de Pastas (se vier do servidor e for a primeira página)
                                if (page === 1 && data.folders && document.getElementById('mediaFolderSelect').options.length <= 1) {
                                    const select = document.getElementById('mediaFolderSelect');
                                    data.folders.forEach(f => {
                                        const opt = document.createElement('option');
                                        opt.value = f.value;
                                        opt.textContent = f.label;
                                        select.appendChild(opt);
                                    });
                                }
                            }

                            if(!data.data || data.data.length === 0) {
                                if(!append) grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-500 text-sm">Nenhuma mídia compatível localizada no WordPress com estes filtros.</div>';
                                document.getElementById('mediaPaginationBox').classList.add('hidden');
                                isLoading = false;
                                return;
                            }

                            stats.innerHTML = `Biblioteca Mapeada: <span class="text-white font-bold">${data.total_items} Imagens</span> (Página ${page} de ${data.total_pages})`;

                            data.data.forEach(img => {
                                const strId = String(img.id);
                                const isProtected = currentlyProtectedIds.includes(strId);
                                
                                const html = `
                                    <label class="cursor-pointer relative group block aspect-square rounded-xl overflow-hidden bg-slate-900 border border-slate-800">
                                        <input type="checkbox" value="${img.id}" class="media-checkbox sr-only" ${isProtected ? 'checked' : ''}>
                                        <div class="absolute inset-0 border-2 border-transparent transition-colors z-20 rounded-xl pointer-events-none">
                                            <div class="overlay-blur absolute inset-0 backdrop-blur-md bg-slate-950/60 opacity-0 transition-opacity flex flex-col items-center justify-center pointer-events-none">
                                                <i class="ph-fill ph-lock-key text-emerald-400 text-2xl drop-shadow-lg scale-[0.8] opacity-0 transition-all duration-300 check-icon-media mt-2"></i>
                                            </div>
                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-3 pt-8 pb-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <p class="text-[9px] text-white font-mono truncate" title="${img.title}">ID: ${img.id} - ${img.title}</p>
                                            </div>
                                        </div>
                                        <img src="${img.url}" loading="lazy" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                                    </label>
                                `;
                                grid.insertAdjacentHTML('beforeend', html);
                            });

                            if (currentPage < data.total_pages) {
                                document.getElementById('mediaPaginationBox').classList.remove('hidden');
                                btnMore.innerHTML = 'Carregar Mais Imagens...';
                            } else {
                                document.getElementById('mediaPaginationBox').classList.add('hidden');
                            }

                        } catch (e) {
                            console.error(e);
                            FrontToast.show('error', "Falha extrema de CORS ou Servidor Inalcançável. Verifique se o plugin no WordPress foi atualizado.");
                            if(!append) workspace.classList.add('hidden');
                        }
                        
                        isLoading = false;
                    };

                    btnLoad.addEventListener('click', () => {
                        currentPage = 1;
                        document.getElementById('mediaSearchInput').value = '';
                        document.getElementById('mediaFolderSelect').innerHTML = '<option value="all">Todas as Pastas</option>'; // reseta a combo
                        loadMedia(currentPage, false);
                    });

                    document.getElementById('btnFilterMedia').addEventListener('click', () => {
                        currentPage = 1;
                        loadMedia(currentPage, false);
                    });

                    document.getElementById('mediaSearchInput').addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            currentPage = 1;
                            loadMedia(currentPage, false);
                        }
                    });

                    document.getElementById('mediaFolderSelect').addEventListener('change', () => {
                        currentPage = 1;
                        loadMedia(currentPage, false);
                    });

                    btnMore.addEventListener('click', () => {
                        if(isLoading) return;
                        currentPage++;
                        loadMedia(currentPage, true);
                    });

                    btnSave.addEventListener('click', async () => {
                        const originalHtml = btnSave.innerHTML;
                        btnSave.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-emerald-400"></i> Aplicando Blur Externo...';
                        btnSave.disabled = true;

                        // Usa uma FormData limpa (ignora inputs escondidos na DOM ou do form container)
                        const formData = new FormData();
                        if (currentlyProtectedIds !== null) {
                            currentlyProtectedIds.forEach(id => {
                                formData.append('protected_ids[]', id);
                            });
                        }
                        formData.append('action', 'save_wp_media');

                        try {
                            const response = await fetch('?route=dashboard', { method: 'POST', body: formData });
                            const json = await response.json();
                            
                            if(json.success) {
                                btnSave.innerHTML = '<i class="ph-bold ph-check text-white"></i> Sucesso PUSH: ' + json.total + ' Protegidas!';
                                btnSave.classList.add('bg-emerald-600', 'border-emerald-500');
                                setTimeout(() => {
                                    btnSave.innerHTML = originalHtml;
                                    btnSave.classList.remove('bg-emerald-600', 'border-emerald-500');
                                    btnSave.disabled = false;
                                }, 3000);
                            } else {
                                FrontToast.show('error', "ERRO NO PUSH: " + json.error);
                                btnSave.innerHTML = originalHtml;
                                btnSave.disabled = false;
                            }
                        } catch(e) {
                            FrontToast.show('error', "ERRO LOCAL: " + e.message);
                            btnSave.innerHTML = originalHtml;
                            btnSave.disabled = false;
                        }
                    });
                });
                </script>
            </div>
        </div> <!-- Fecha flex-1 overflow-y-auto -->
            
        <!-- ====== FOOTER ====== -->
            <div class="mt-16 text-center text-[10px] font-mono text-slate-500 uppercase tracking-widest pb-4">
                Front18 Pro SaaS - Camada de Defesa Operacional
            </div>
        </div>
    </main>

    <script>
        function selectBlurPreset(mode, element) {
            const defaultSel = 'img, video, iframe, [data-front18="locked"]';
            const eleSel = 'img, video, iframe, [data-front18="locked"], .elementor-loop-container article, .elementor-widget-loop-builder .elementor-post';
            const input = document.getElementById('blur_selector_input');
            const area = document.getElementById('custom_blur_area');
            
            // Reset state
            document.querySelectorAll('input[name="_blur_preset"]').forEach(radio => {
                let label = radio.closest('label');
                let icon = label.querySelector('.preset-icon');
                let title = label.querySelector('.preset-title');
                label.className = 'flex p-3 border rounded-xl cursor-pointer transition-all bg-slate-900 border-slate-700 hover:border-slate-500';
                icon.className = 'preset-icon ph-fill ph-check-circle opacity-0 text-emerald-500';
                title.className = 'preset-title text-sm font-bold text-slate-300 flex items-center gap-2';
            });
            
            // Apply new state
            let label = element;
            let icon = label.querySelector('.preset-icon');
            let title = label.querySelector('.preset-title');
            
            if (mode === 'custom') {
                label.className = 'flex p-3 border rounded-xl cursor-pointer transition-all bg-amber-900/10 border-amber-500';
                icon.className = 'preset-icon ph-fill ph-check-circle opacity-100 text-amber-500';
                title.className = 'preset-title text-sm font-bold text-amber-400 flex items-center gap-2';
                area.classList.remove('hidden');
            } else {
                label.className = 'flex p-3 border rounded-xl cursor-pointer transition-all bg-emerald-900/10 border-emerald-500';
                icon.className = 'preset-icon ph-fill ph-check-circle opacity-100 text-emerald-500';
                title.className = 'preset-title text-sm font-bold text-emerald-400 flex items-center gap-2';
                area.classList.add('hidden');
                if (mode === 'default') input.value = defaultSel;
                if (mode === 'elementor') input.value = eleSel;
            }
        }

        const titles = {
            'home': 'Visão Geral', 'logs': 'Cadeia de Custódia Auditável', 'reports': 'Dossiê Jurídico (PDF)', 
            'domains': 'Gestão de Domínios', 'settings': 'Configurações de Blindagem', 'appearance': 'Personalização de Marca e UI', 'privacy': 'Portal LGPD e Cookies', 'suspicious': 'Atividade Suspeita / Abuso', 
            'billing': 'Plano B2B e Assinatura', 'api': 'Integração Edge API', 'wp': 'Sincronização WordPress'
        };
        
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            const targetContent = document.getElementById('tab-' + tabId);
            if(targetContent) targetContent.classList.add('active');
            
            document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
            const targetBtn = document.getElementById('tab-btn-' + tabId);
            if(targetBtn) targetBtn.classList.add('active');
            
            if(titles[tabId]) {
                document.getElementById('page-title').innerText = titles[tabId];
            }

            // Atualiza a URL sem fazer o navegador pular (Scroll Jump)
            if (history.pushState) {
                history.pushState(null, null, '#' + tabId);
            } else {
                window.location.hash = '#' + tabId;
            }

            // Backup Infalível na Memória do Navegador (Sobrevive a redirects POST do PHP)
            localStorage.setItem('front18_client_current_tab', tabId);
        }

        // Recuperar Aba ao carregar a página (F5 ou Voltar de Redirect)
        document.addEventListener('DOMContentLoaded', () => {
            let hash = window.location.hash.substring(1);
            let memory = localStorage.getItem('front18_client_current_tab');
            
            let targetTab = hash ? hash : (memory ? memory : 'home');

            if (titles[targetTab]) {
                switchTab(targetTab);
            } else {
                switchTab('home');
            }
        });
    </script>
    
    <!-- Sistema Global de Notificações VIP (Toasts) -->
    <div id="front18-toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

    <script>
    const FrontToast = {
        show: function(type, message) {
            const container = document.getElementById('front18-toast-container');
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl border backdrop-blur-md shadow-2xl transition-all duration-300 transform translate-y-10 opacity-0 pointer-events-auto max-w-[320px] w-max ml-auto`;
            
            let icon = '';
            if (type === 'success') {
                toast.classList.add('bg-emerald-950/90', 'border-emerald-500/30', 'text-emerald-50');
                icon = '<i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i>';
            } else if (type === 'error') {
                toast.classList.add('bg-rose-950/90', 'border-rose-500/30', 'text-rose-50');
                icon = '<i class="ph-fill ph-warning-circle text-rose-400 text-xl"></i>';
            } else if (type === 'warning') {
                toast.classList.add('bg-amber-950/90', 'border-amber-500/30', 'text-amber-50');
                icon = '<i class="ph-fill ph-warning text-amber-400 text-xl"></i>';
            } else {
                toast.classList.add('bg-slate-900/90', 'border-slate-700/50', 'text-slate-100');
                icon = '<i class="ph-fill ph-info text-indigo-400 text-xl"></i>';
            }
            
            toast.innerHTML = `
                <div class="shrink-0">${icon}</div>
                <div class="text-[11px] font-mono leading-relaxed">${message}</div>
                <button onclick="this.parentElement.remove()" class="shrink-0 text-slate-400 hover:text-white transition-colors p-1"><i class="ph-bold ph-x"></i></button>
            `;
            
            container.appendChild(toast);
            
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            });
            
            setTimeout(() => {
                if(!toast) return;
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 6000);
        }
    };
    </script>
</body>
</html>
