<?php
// Front18 Pro - Termos de Uso e Responsabilidade B2B
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso e Responsabilidade | Front18 Pro</title>
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
<body class="bg-[#020617] text-slate-300 min-h-screen flex flex-col selection:bg-primary-500/30">

    <header class="h-20 border-b border-slate-800 flex items-center px-8 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
        <a href="?route=landing" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <span class="text-xl font-black tracking-tight text-white">Front18<span class="text-primary-500">.</span></span>
        </a>
        <div class="ml-auto flex gap-4">
            <a href="?route=landing" class="text-sm font-bold text-slate-400 hover:text-white transition-colors">Voltar ao Início</a>
        </div>
    </header>

    <main class="flex-1 max-w-4xl mx-auto w-full px-6 py-16 text-slate-300 leading-relaxed">
        
        <h1 class="text-4xl font-black text-white mb-4">Termos de Uso e Responsabilidade (SaaS B2B)</h1>
        <p class="text-sm text-slate-500 mb-12 font-mono uppercase tracking-widest border-b border-slate-800 pb-4">Última Atualização: 22 de Março de 2026 | Documento Estrutural Público</p>

        <section class="space-y-8">
            <div>
                <h2 class="text-xl font-bold text-white mb-3">1. Natureza do Serviço e Limitações Tecnológicas</h2>
                <p>O <strong>Front18 Pro</strong> atua EXCLUSIVAMENTE como uma barreira arquitetural de software (WAF e camada Front-End) desenhada para agregar atrito diligente ("friction-based due diligence") contra o acesso automatizado, acidental ou desavisado de menores a conteúdos controlados. O sistema NÃO é um verificador pericial de identidade estatal, NÃO emite laudos biométricos e NÃO extingue o risco de fraude por parte de usuários de má-fé operando sob a tutela da falsa declaração ou engenharia reversa sofisticada.</p>
                <p class="mt-2 text-indigo-300 bg-indigo-500/10 p-4 rounded-lg border border-indigo-500/20 text-sm">
                    <strong>ISENÇÃO TÉCNICA CLARA:</strong> Não prometemos barreiras "inquebráveis" ou "infalíveis", vez que a própria infraestrutura da web (Navegadores, Ferramentas de Desenvolvedor e Redes Distribuídas) permite manipulações avançadas do lado do cliente (Client-Side). Modos como o "Isolamento Estrito" mitigam drasticamente, mas não prometem erradicação definitiva de ferramentas cibernéticas intrusivas.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">2. Transferência de Autoria e Responsabilidade Civil</h2>
                <p>Ao implementar os SDKs ou APIs do Front18 Pro via chaves ("API Keys"), a empresa contratante do software (O "Cliente B2B / Controlador") declara ser a ÚNICA detentora e responsável legal pelo teor do material eletrônico protegido, da origem do tráfego e do cumprimento de diretrizes sancionatórias do seu país sede.</p>
                <ul class="list-disc pl-5 mt-2 space-y-2 text-sm text-slate-400">
                    <li>O Front18 opera como "Processador Limitado", oferecendo apenas a telemetria do filtro de entrada.</li>
                    <li>Toda defesa jurídica civil oriunda do conteúdo resguardado pelo nosso gateway é responsabilidade exclusiva da marca contratante. O Front18 fornecerá os "Dossiês de Log" apenas como material probatório de defesa periférica, sem atestar o valor sentencial sobre a corte julgadora.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">3. Coleta Mínima e Cadeia de Custódia Imutável</h2>
                <p>Nossa plataforma compromete-se com a coleta mínima via o princípio "Privacy by Design". Dados como o endereço numérico da rede visitante (IP) são processados visando EXCLUSIVAMENTE criar o "Hash de Registro Contratual" exigido para formar prova passiva de consentimento em casos de lide jurídica (Legítimo Interesse). Jamais comercializamos esse diário de acesso ("Access Logs") para Data Brokers ou ecossistemas de publicidade. Estes dados formam os "Laudos em PDF" de defesa do nosso contratante e são posteriormente sobrepostos (rotação cíclica dependente do plano contratado).</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">4. Indisponibilidade Sistêmica (SLA "AS-IS")</h2>
                <p>Embora tenhamos arquitetado nossa "Edge API" para altíssima resiliência sob ataques massivos, redes são suscetíveis a quedas transcontinentais. O serviço Front18 é contratado "NO ESTADO EM QUE SE ENCONTRA" (AS-IS). Interrupções, falhas momentâneas de latência no processamento criptográfico (criptografia XOR de Front-End) ou lentidão momentânea no desbloqueio visual da Mídia não geram direito legal indenizatório ao Controlador B2B ou aos seus respectivos clientes finais (Usuários).</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">5. Extradição e Modos Paranoicos de Rede (WAF)</h2>
                <p>Planos "Advanced" utilizam banimentos de Borda de Rede (Cloud Firewall). Caso o usuário final ative sistemas avançados de ofuscação (VPNs profundas, Tor e proxies em cascata), a tecnologia poderá gerar falsos-positivos na intenção de blindar o núcleo do Controlador de robôs de mineração infantil ("Anti-Scraping"). Esta severidade é ativada voluntariamente pelo painel do Cliente B2B e não confunde falha de sistema, sendo simétricao ao rigor das barreiras exigidas.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">6. Políticas de Assinatura, Faturamento e Cancelamento</h2>
                <p>O Front18 Pro é comercializado globalmente sob a licença de Software as a Service (SaaS). O faturamento ocorre de forma recorrente e antecipada. A inadimplência resulta inicialmente em <em>Soft-Lock</em> (exibição de aviso de licença inativa aos visitantes) e subsequentemente no <em>Hard-Drop</em> (bloqueio total do portal protegido). O cancelamento pode ser efetuado a qualquer momento via Dashboard, cessando futuras cobranças, contudo, não haverá estorno pró-rata para frações de meses já iniciados, dada a natureza imediata da locação de reserva de infraestrutura descentralizada.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">7. Licença Limitada e Propriedade Intelectual (IP)</h2>
                <p>Entregamos ao Cliente B2B uma licença temporária, revogável, não-exclusiva e intransferível de uso do SDK e das APIs. O <em>core-engine</em>, scripts de ofuscação XOR, layouts de injeção defensiva e base de dados do Front18 Pro permanecem como Propriedade Intelectual irrestrita da nossa corporação. É veementemente proibida a engenharia reversa para clonar ou bifurcar (fork) a tecnologia, sob pena de bloqueio sistêmico sem aviso prévio e processos de proteção de patentes/copyright.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">8. Teto de Responsabilidade e Indenizações (Limitation of Liability)</h2>
                <p>Em hipótese alguma, ou sob nenhuma teoria jurídica formal, o Front18 Pro, seus fundadores ou diretores poderão ser responsabilizados por danos indiretos, incidentais, punitivos ou consequentes gerados pelo uso ou incapacidade de uso do serviço. Se processado, a responsabilidade financeira global e agregada da plataforma com o Cliente B2B <strong>limita-se estritamente ao montante total pago pelo Cliente nos últimos 3 (três) meses</strong> que antecederam o evento motivador da lide.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">9. Moderação Coercitiva e Quebra de Contrato</h2>
                <p>A Front18 Pro reserva-se ao direito soberano e intransponível de suspender a emissão de APIs ao locatário B2B se constatado: a) Uso da ferramenta para acobertar crimes digitalizados inafiançáveis fora do escopo do mero entretenimento adulto consentido; b) Sabatina abusiva dos nossos nós de rede para testes de penetração não-autorizados (DDoS proposital); c) Utilização de nossa logomarca fora das restrições de Co-Branding estipulados via SaaS.</p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">10. Foro de Eleição e Jurisdição Competente</h2>
                <p>Qualquer disputa que surja inerente a este Contrato Maestro ou pelo uso das APIs do Front18 deverá ser dirimida de forma arbitral e confidencial ou, se inviável, submetida ao litígio em comarcas especializadas de tecnologia e foro de Delaware (ou do estado primário de emissão da licença no seu respectivo país host). O documento legal e as comunicações periciais se darão integralmente no idioma acordado durante o faturamento do serviço.</p>
            </div>
            
            <div class="bg-red-500/10 border-l-4 border-red-500 p-6 rounded-r-xl mt-8 shadow-lg shadow-red-500/5">
                <h3 class="font-black text-red-500 mb-3 uppercase tracking-wide text-sm flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Aceitação Formal Plena (Binding Agreement)</h3>
                <p class="text-red-200 text-sm leading-relaxed mb-4">
                    A leitura destes Termos constitui um <strong>Contrato Legalmente Vinculativo</strong>. O simples ato voluntário de cadastrar-se na plataforma, faturar um de nossos SDKs ou injetar nossa arquitetura (via Tag <code>&lt;script src="Front18.js"&gt;</code>) em seu ecossistema digital formaliza sua aceitação unânime, explícita e irretratável.
                </p>
                <p class="text-red-200 text-sm leading-relaxed font-medium">
                    Consolida-se, por vias de fato, o imediato repasse do ônus pericial à sua Pessoa Jurídica (controladora do website). <strong>Caso você ou seus advogados discordem de qualquer tese ou limitação aqui listada, interrompa a implantação imediatamente e encerre sua assinatura via painel. O uso contínuo chancelará a sua total anuência.</strong>
                </p>
            </div>

        </section>

    </main>

    <footer class="border-t border-slate-800 bg-[#020617] text-slate-500 text-sm py-12 text-center mt-20">
        <p>Front18 Pro &reg; 2026. Todos os direitos reservados à Infraestrutura Front18 SaaS.</p>
    </footer>

</body>
</html>

