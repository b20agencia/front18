<?php
/**
 * Arquivo: security.php | Controlador de Escudos Perimetrais WAF (Web Application Firewall)
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnologia e Privacidade - AgeGate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f8fafc; }
        .tech-grid { background-image: radial-gradient(circle at 1px 1px, #1e293b 1px, transparent 0); background-size: 40px 40px; }
        .glass-card { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .gradient-text { background: linear-gradient(135deg, #a855f7, #6366f1, #3b82f6); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="tech-grid min-h-screen flex flex-col pt-12 md:pt-20">

    <div class="max-w-3xl mx-auto px-6 pb-24 relative z-10 w-full">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-sm font-semibold tracking-wide mb-8">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            Transparência & Privacidade
        </div>

        <!-- Header -->
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-6">Como a <span class="gradient-text">Tecnologia</span> AgeGate Mitiga Riscos</h1>
        <p class="text-lg text-slate-400 leading-relaxed mb-12">Nossa estrutura arquitetural foi desenhada sob os pilares da mitigação forense. A verificação local serve como uma <b>barreira dissuasória inteligente</b> contra riscos jurídicos, garantindo conformidade sem desrespeitar os dados do usuário.</p>

        <!-- Cards -->
        <div class="space-y-6">
            
            <!-- Mitigação Assertiva -->
            <div class="glass-card rounded-2xl p-8 transition-all hover:border-slate-700">
                <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-3 text-slate-200">Mitigação Assertiva, Não Certeza Absoluta</h3>
                <p class="text-slate-400 leading-relaxed">A tecnologia biométrica corporativa atua no campo da previsibilidade. A solução <b>AgeGate atua como um previsor estatístico</b> (Proxy Prediction), afastando robôs e acessos infantis óbvios. <strong>Nosso algoritmo não oferece uma garantia irrefutável sobre a idade civil (como faz um documento do governo)</strong>, mas estabelece uma sólida parede de "boa-fé jurídica". Essa rastreabilidade técnica diminui esmagadoramente a responsabilidade corporativa do lojista na Justiça.</p>
            </div>

            <!-- LGPD -->
            <div class="glass-card rounded-2xl p-8 transition-all hover:border-slate-700">
                <div class="w-12 h-12 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-3 text-slate-200">Privacidade Local (Edge Processing / LGPD)</h3>
                <p class="text-slate-400 leading-relaxed">Para não lidarmos com a transferência criminosa de fotos, toda a inferência matemática ocorre localmente. O motor algorítmico navega apenas pela memória volátil do aparelho celular ou computador do usuário:</p>
                <div class="mt-4 bg-slate-900/50 p-4 border border-slate-800 rounded-lg">
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li class="flex items-start gap-3"><svg class="w-5 h-5 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <span><b>Zero Imagens Retidas:</b> Nenhuma foto ou fluxo de vídeo atravessa a internet para nossos servidores.</span></li>
                        <li class="flex items-start gap-3"><svg class="w-5 h-5 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <span><b>Métricas Anônimas:</b> Apenas os <i>inteiros processados</i> (Idade e Precisão Geográfica) são enviados ao cofre do AgeGate, sem vinculação ao CPF.</span></li>
                        <li class="flex items-start gap-3"><svg class="w-5 h-5 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <span><b>Oblitéração:</b> O stream de hardware morre instantaneamente na navegação (Menos de 2 segundos de execução).</span></li>
                    </ul>
                </div>
            </div>

            <!-- Auditoria -->
            <div class="glass-card rounded-2xl p-8 transition-all hover:border-slate-700">
                <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-3 text-slate-200">Trilha de Auditoria Blindada</h3>
                <p class="text-slate-400 leading-relaxed">Em caso de litígio ou exigência legal (Procons Regionais ou Ministério Público), o AgeGate gera um Hash Criptográfico. Este documento inviolável certifica os registros de "Opt-in", o modelo computacional preditivo que bloqueou o dano, e livra o lojista do ônus solitário da imprudência do comprador.</p>
            </div>

        </div>
    </div>
</body>
</html>
