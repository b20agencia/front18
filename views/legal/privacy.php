<?php
// Front18 Pro - Política de Privacidade e Mapeamento de Cookies (LGPD/GDPR)
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <link rel="icon" type="image/png" href="public/img/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy & Cookie Notice | Front18 Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93bffd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"} }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#020617] text-slate-300 min-h-screen flex flex-col selection:bg-emerald-500/30">

    <header class="h-20 border-b border-slate-800 flex items-center px-8 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
        <a href="?route=landing" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <span class="text-xl font-black tracking-tight text-white">Front18<span class="text-emerald-500">.</span></span>
        </a>
        <div class="ml-auto flex gap-4">
            <a href="?route=landing" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">Retornar</a>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-6 py-16 text-slate-300 leading-relaxed">
        
        <div class="mb-12">
            <h1 class="text-4xl font-black text-white mb-4">Privacidade B2B e Injeção de Cookies</h1>
            <p class="text-sm text-slate-500 font-mono uppercase tracking-widest border-b border-slate-800 pb-4">Nossa conformidade e compromisso com o seu usuário final</p>
        </div>

        <section class="space-y-12">
            <!-- Privacidade (Minimização) -->
            <div>
                <h2 class="text-xl font-bold text-white mb-3 text-emerald-400">1. A Cultura da Minimização de Dados</h2>
                <p>Nossa fundação jurídica preza pela <strong>minimização (Data Minimization)</strong> estrita e cega. Ao contrário de provedores tradicionais de Analytics, o ecossistema Front18 <strong>não</strong> rastreia, perfila, desenha comportamentos mercadológicos ou processa atributos biográficos do seu visitante final (Titular de Dados). Nenhuma informação é trocada por enriquecimento de Ads em nossas bases.</p>
                <div class="flex gap-4 mt-4 bg-slate-900 border border-slate-800 p-5 rounded-xl">
                    <i class="ph-fill ph-fingerprint text-3xl text-slate-400 shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-sm text-white mb-1">Cadeia de Hash Limitado (Telemetria Autenticada)</h4>
                        <p class="text-[11px] text-slate-400 leading-tight">Os endereços IP e OS da ponta conectada são transmutados em "Assinaturas Hash Criptográficas" em curtos espaços de tempo. Eles existem puramente acobertados pela base legal do <em>Legítimo Interesse Produtivo e Proteção ao Patrimônio Digital</em>, necessários exclusivamente para justificar juridicamente o bloqueio (Firewall de Restrição Etária) e comprovar a Diligência de Barreira em caso de disputas legais contra o controlador.</p>
                    </div>
                </div>
            </div>

            <!-- Como processamos os Logs do Cliente -->
            <div>
                <h2 class="text-xl font-bold text-white mb-3 text-emerald-400">2. Destino e Duração da Custódia em Servidor</h2>
                <p>O <em>Log Engine Forense</em> do nosso gateway é disponibilizado diretamente ao locatário SaaS na forma de "Dossiês em PDF Mensais". A custódia original atrelada e as listagens transientes da API do cliente ficam abrigadas em bancos de dados distribuídos rigidamente controlados. Devido à não-essencialidade da perpetuação, todos os logs brutos vinculáveis podem sofrer apagamento programado e irreversível da nuvem após expiração de suas tabelas de quarentena, exigindo o repasse da proteção à máquina local do cliente contratante.</p>
            </div>

            <!-- Aviso de Cookies Formal (LGPD / GDPR) -->
            <div class="border-t border-slate-800 pt-12">
                <i class="ph-fill ph-cookie text-4xl text-amber-500 mb-4 inline-block drop-shadow-lg"></i>
                <h2 class="text-3xl font-black text-white mb-4">Aviso Oficial de Cookies e Armazenamento Oculto</h2>
                
                <p class="mb-4">Estejam avisados todos os clientes (Pessoas Jurídicas Administradoras do nosso Painel) e Visitantes (Acionadores do SDK em sites terceiros) do uso contínuo de matrizes locais (Local Storage, HTTP-Only Cookies) voltadas exclusivamente à segurança sistêmica.</p>

                <div class="mt-6 border border-slate-800 rounded-2xl overflow-hidden bg-slate-900/50">
                    <table class="w-full text-left text-sm font-mono">
                        <thead class="bg-slate-900">
                            <tr>
                                <th class="p-4 font-bold text-slate-300">Variável Local</th>
                                <th class="p-4 font-bold text-slate-300">Mecanismo</th>
                                <th class="p-4 font-bold text-slate-300">Tempo / Finalidade Justa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-4 text-emerald-400 font-bold whitespace-nowrap">ag_srv_token</td>
                                <td class="p-4 text-slate-500">Local Array</td>
                                <td class="p-4 text-slate-400"><strong class="text-white">Estritamente Necessário (Sessão Backend).</strong> Armazena o Criptograma JWT do motor PHP para liberar o HTML criptografado sem re-avaliamentos (Zero-Trust Bypass Seguro). Expiração: Conforme configurado pelo Locatário B2B (1 Hora a 90 Dias).</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-4 text-emerald-400 font-bold whitespace-nowrap">Front18_verified_ux</td>
                                <td class="p-4 text-slate-500">Local/Session Storage</td>
                                <td class="p-4 text-slate-400"><strong class="text-white">Estritamente Necessário (UX Flow).</strong> Memoriza fluidamente uma aba para autorizar o Destranque do Blur/CSS Overlay lateral, poupando a máquina humana de retrabalho constante contra fadiga digital. Expiração: Client-Side.</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="p-4 text-emerald-400 font-bold whitespace-nowrap">PHPSESSID</td>
                                <td class="p-4 text-slate-500">HttpOnly / Secure</td>
                                <td class="p-4 text-slate-400"><strong class="text-white">Estritamente Necessário (Painel Root).</strong> Usado EXCLUSIVAMENTE para viabilizar login, autentificação, tokenização de conta Administradora SaaS e funções financeiras. Expira no logout ou timeout de navegação inativa.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-6 rounded-r-xl mt-8">
                <p class="text-emerald-200 text-sm">
                    <strong>Zero-Consentimento Explícito Comercial Exigido:</strong> Todos os vetores de armazenamento supramencionados isentam-se da obrigatoriedade do banner intrusivo de "Opções de Tracking de Marketing" (Consent Management Platform - CMP), uma vez que se enquadram rigorosamente na diretriz de "Sistemas Estritamente Necessários Funcionais e de Segurança de Rede", fundamentais para blindar legalmente o Serviço Prestado e assegurar resquícios contratuais base entre Hospedeiro Digital e Front18 Pro.
                </p>
            </div>
            
            <p class="text-xs text-slate-500 text-center uppercase tracking-widest mt-10">O Data Protection Officer da Front18 pode ser acionado em vias eletrônicas fechadas para consultas extensivas de DPO.</p>

        </section>

    </main>

    <!-- Cookie Micro-Banner B2B para o Site Próprio -->
    <div id="selfCookieNotice" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-slate-900 border border-slate-700 p-4 rounded-2xl shadow-2xl z-50 flex flex-col md:flex-row items-center gap-6 max-w-2xl w-[90%] md:w-full transition-transform translate-y-24 opacity-0 hidden">
        <div class="flex-1">
            <h4 class="font-bold text-white text-sm mb-1">Nosso App usa Cookies Funcionais Base</h4>
            <p class="text-xs text-slate-400 leading-tight">Ao ingressar nas contas de Dashboard e Planos, nós travamos sua sessão localmente em navegadores corporativos por pura criptografia, sendo estritamente funcional à segurança.</p>
        </div>
        <button onclick="document.getElementById('selfCookieNotice').classList.add('translate-y-24', 'opacity-0'); setTimeout(()=>document.getElementById('selfCookieNotice').remove(), 300)" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 px-6 rounded-xl text-sm whitespace-nowrap transition-colors shadow-md">Compreendido</button>
    </div>
    
    <script>
        // Fake little presentation of the banner when entering the page organically
        setTimeout(() => {
            const banner = document.getElementById('selfCookieNotice');
            banner.classList.remove('hidden');
            requestAnimationFrame(() => banner.classList.remove('translate-y-24', 'opacity-0'));
        }, 1500);
    </script>

</body>
</html>

