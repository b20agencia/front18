<?php
/**
 * Arquivo: docs.php | Central de Ajuda e SDK Docs Disponível para os Clientes
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

// Busca a origem cadastrada do cliente para jogar a apiKey real no tutorial
$stmt = $pdo->prepare("SELECT * FROM saas_origins WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['saas_admin']]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

$apiKeyDemo = $config ? $config['api_key'] : 'SUA_CHAVE_AQUI_CRIADA_NO_PAINEL';
$domainDemo = $config ? $config['domain'] : 'seudominio.com.br';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <link rel="icon" type="image/png" href="public/img/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front18 SDK - Documentação Oficial</title>
    <!-- Tailwind CSS -->
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background-color: #020617; color: #f8fafc; }
        .glass-panel { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .code-block { background: #0b1120; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 0.75rem; padding: 1.5rem; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; overflow-x: auto; }
        .step-number { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LCAyNTUsIDI1NSwgMC4wNSkiLz48L3N2Zz4=')]">

    <!-- Header -->
    <header class="h-16 flex items-center justify-between px-8 bg-slate-900 border-b border-indigo-500/20 sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="?route=dashboard" class="flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors">
                <i class="ph-bold ph-arrow-left"></i>
            </a>
            <div class="w-6 h-6 rounded bg-gradient-to-br from-indigo-500 to-primary-600 flex items-center justify-center">
                <i class="ph-bold ph-book-open text-white text-xs"></i>
            </div>
            <h1 class="font-bold text-md tracking-tight">Manual de Integração <span class="text-indigo-400 font-normal">SaaS</span></h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="?route=dashboard" class="text-indigo-400 hover:text-indigo-300 text-xs font-semibold transition-all flex items-center gap-1"><i class="ph-bold ph-squares-four"></i> Meu Dashboard</a>
        </div>
    </header>

    <main class="flex-1 p-8 overflow-y-auto w-full max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center p-3 rounded-full bg-indigo-500/10 mb-4 border border-indigo-500/20">
                <i class="ph-fill ph-code text-indigo-400 text-3xl"></i>
            </div>
            <h1 class="text-4xl font-extrabold text-white mb-4">Guia Definitivo: <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-primary-400">Implementando a Blindagem</span></h1>
            <p class="text-slate-400 text-lg leading-relaxed">Aprenda visualmente como conectar nosso motor de restrição (Front18 SDK) via Javascript no seu WordPress, Site Institucional ou Aplicação Pessoal.</p>
        </div>

        <div class="space-y-12 pb-20">

            <!-- STEP 1 -->
            <div class="glass-panel rounded-3xl p-8 relative overflow-hidden border-indigo-500/20 shadow-xl shadow-indigo-500/5">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex gap-6 items-start relative z-10">
                    <div class="step-number bg-indigo-500 text-white text-lg shadow-lg shadow-indigo-500/30">1</div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">Instância & Domínio</h2>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            O nosso sistema B2B possui Defesa Anti-Pirata. Se um desenvolvedor chinês copiar seu código, o SDK <b>não vai abrir</b> porque não rodará no mesmo Domínio Base Validado. <br><br>
                            Para funcionar, você precisa:
                        </p>
                        
                        <div class="bg-slate-900 border border-slate-700/50 rounded-xl p-5 mb-4 border-l-4 border-l-amber-500">
                            <ul class="space-y-3 text-sm text-slate-300">
                                <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Ir na janela <a href="?route=dashboard" class="text-indigo-400 underline">Meu Dashboard</a>.</li>
                                <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Preencher o Box "Domínio Hospedeiro Restrito" com o site exato onde seu sistema rodará.</li>
                                <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Pegar a sua <b>Authorization API Key</b> gerada após Salvar.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="glass-panel rounded-3xl p-8 relative overflow-hidden border-primary-500/20 shadow-xl shadow-primary-500/5">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex gap-6 items-start relative z-10">
                    <div class="step-number bg-primary-500 text-white text-lg shadow-lg shadow-primary-500/30">2</div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">Injeção do Coração (Core SDK)</h2>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Com sua API Key gerada (você atualmente já tem a chave se cadastrou as URLs), injete o Script de Rastreamento (O SDK). O lugar ideal para colocar esse código é antes do fechamento da Tag <code>&lt;/body&gt;</code> do seu Site ou no painel <i>"Custom JS"</i> do seu CMS.
                        </p>
                        
                        <div class="code-block relative group">
                            <button onclick="navigator.clipboard.writeText(document.getElementById('c1').innerText); alert('Copiado!')" class="absolute top-3 right-3 text-slate-500 hover:text-white transition-colors bg-white/5 p-2 rounded-lg"><i class="ph-bold ph-copy"></i></button>
                            <pre id="c1"><span class="text-slate-500">&lt;!-- Front18 Pro: Proteção Server-Side Head Tag --&gt;</span>
<span class="text-blue-400">&lt;script</span> <span class="text-green-300">src</span><span class="text-white">=</span><span class="text-amber-300">"https://Front18.test/public/sdk/Front18.js"</span>
        <span class="text-green-300">data-api-key</span><span class="text-white">=</span><span class="text-amber-300">"<?= htmlspecialchars($apiKeyDemo) ?>"</span>
        <span class="text-green-300">data-terms-version</span><span class="text-white">=</span><span class="text-amber-300">"v1.0"</span>
        <span class="text-green-300">data-terms-url</span><span class="text-white">=</span><span class="text-amber-300">"<?= htmlspecialchars($config['terms_url'] ?? 'https://seusite.com/termos.html') ?>"</span>
        <span class="text-green-300">data-privacy-url</span><span class="text-white">=</span><span class="text-amber-300">"<?= htmlspecialchars($config['privacy_url'] ?? 'https://seusite.com/privacidade.html') ?>"</span>
        <span class="text-blue-400">defer&gt;&lt;/script&gt;</span></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3 -->
            <div class="glass-panel rounded-3xl p-8 relative overflow-hidden border-emerald-500/20 shadow-xl shadow-emerald-500/5">
                <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex gap-6 items-start relative z-10">
                    <div class="step-number bg-emerald-500 text-white text-lg shadow-lg shadow-emerald-500/30">3</div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">Engatilhando a Blindagem de Midia (Backend)</h2>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Aqui é onde nós vencemos juridicamente 99% dos concorrentes. Onde estava o seu vídeo adulto explícito ou sua foto VIP, nós **REMOVEMOS O HTML DE ORIGEM**.<br><br>
                            Você simplesmente coloca uma DIV Vazia avisando qual ID a Criptografia deve buscar. **O Front18 não permite renderizar Mídia no HTML original**, ele joga depois via API se o cara assinar o Termo.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl">
                                <p class="text-[10px] uppercase font-bold text-red-400 mb-2 flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> O jeito ERRADO Antigo (Perigoso)</p>
                                <pre class="font-mono text-xs text-red-200/60 leading-relaxed overflow-x-auto text-wrap">
&lt;!-- Uma criança aperta F12 e rouba a URL --&gt;
&lt;div class="borrar-tela"&gt;
   &lt;video src="meu_video_+18.mp4"&gt;
&lt;/div&gt;</pre>
                            </div>
                            
                            <div class="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl">
                                <p class="text-[10px] uppercase font-bold text-emerald-400 mb-2 flex items-center gap-1"><i class="ph-bold ph-shield-check"></i> O Jeito Front18 Pro (Isento)</p>
                                <pre class="font-mono text-xs text-emerald-200/80 leading-relaxed overflow-x-auto text-wrap">
&lt;!-- Código Vazio blindado por Data-API. --&gt;
&lt;div data-Front18="locked" data-id="meu_video_1"&gt;
   Aguardando Modal de Risco Jurídico...
&lt;/div&gt;</pre>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 mb-4">*Nota: O Gateway B2B saberá que ele precisa empurrar o HTML final de `meu_video_1` porque seu Webmaster (Super Admin) gravou isso dentro da Gestão de Conteúdos no Painel Geral.</p>

                    </div>
                </div>
            </div>

            <div class="text-center pt-8 border-t border-white/10">
                <i class="ph-fill ph-check-circle text-5xl text-emerald-500 mb-4 block"></i>
                <h3 class="text-2xl font-bold text-white mb-2">Tudo Pronto!</h3>
                <p class="text-slate-400 text-sm mb-6 max-w-lg mx-auto">Com essa trilha, o seu ambiente HTML5 fica impenetrável. Os IPs serão logados e processados pelo SDK da sua conta SaaS.</p>
                <a href="?route=dashboard" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-200 transition-colors shadow-xl">Voltar ao Meu Dashboard <i class="ph-bold ph-arrow-right"></i></a>
            </div>

        </div>
    </main>

</body>
</html>

