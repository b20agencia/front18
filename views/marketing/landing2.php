<?php
require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';
try {
    Database::setup();
    $pdo = Database::getConnection();
    // Exibindo apenas planos com `price` pro site público.
    $planos = $pdo->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    $planos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="public/img/favicon.png">

    <!-- SEO Principal -->
    <title>Front18 — Verificação de Idade por Biometria Facial | Conformidade Lei FELCA, LGPD e Decreto 12.880</title>
    <meta name="description"
        content="Seu site pode ser MULTADO em até R$ 50 milhões. O Front18 é o sistema de verificação de idade por biometria facial que garante conformidade com a Lei 15.211/2025 (FELCA), Decreto 12.880/2026 e LGPD. Instale em 30 segundos.">
    <meta name="keywords" content="verificação de idade, biometria facial, lei felca, lgpd, age gate">
    <meta name="robots" content="index, follow">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Front18",
        "applicationCategory": "SecurityApplication",
        "operatingSystem": "Web",
        "description": "Sistema de verificação de idade por biometria facial para conformidade com Lei FELCA",
        "offers": {
            "@type": "AggregateOffer",
            "lowPrice": "600",
            "highPrice": "3000",
            "priceCurrency": "BRL",
            "offerCount": "4"
        }
    }
    </script>

    <!-- Nosso CSS já integrado com estilo GoAdopt e fontes da LGPD -->
    <link rel="stylesheet" href="/public/css/new.css?v=<?= time()?>">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="nav">
        <div class="nav-inner">
            <div class="nav-brand">
                <a href="?route=landing2" style="display:flex; align-items:center;">
                    <img src="public/img/logo.png" alt="Front18 Logo" style="height: 24px; object-fit: contain;">
                </a>
            </div>
            <div class="nav-links">
                <a href="#riscos">Riscos</a>
                <a href="#solucao">Solução</a>
                <a href="#recursos">Recursos</a>

                <a href="#planos">Planos</a>
                <a href="#faq">FAQ</a>
                <a href="?route=login" class="nav-btn">Acessar Painel</a>
            </div>
        </div>
    </nav>

    <!-- URGENCY BANNER -->
    <div class="urgency-banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            style="vertical-align:-3px">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg> <strong>Prazo legal esgotando:</strong> A Lei 15.211/2025 (FELCA) já está em vigor. Sites sem verificação
        de idade podem ser multados a qualquer momento.
    </div>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-eyebrow"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"
                style="vertical-align:-2px">
                <circle cx="12" cy="12" r="10" />
            </svg> ATENÇÃO: Seu site pode estar IRREGULAR agora</div>
        <h1>
            Seu site pode levar uma<br>
            <span class="highlight">multa de R$ 50 milhões</span><br>
            <span class="grad">Resolva em 30 segundos</span>
        </h1>
        <p class="lead">
            A <strong>Lei FELCA (15.211/2025)</strong> exige verificação de idade em sites com conteúdo restrito.
            O <strong>Decreto 12.880/2026</strong> regulamentou com prazo imediato. O Front18 é a <strong>catraca de
                biometria facial</strong> que garante conformidade total.
        </p>
        <div class="hero-ctas">
            <a href="#planos" class="btn-lg btn-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path
                        d="M8.5 14.5A2.5 2.5 0 0 0 11 12c-2.2-.6-3.8-2.6-4.5-5-.4-1.2-1.3-1.8-2.5-1.5a5.5 5.5 0 0 0 10 9c.7-2.2 0-4.6-2-6 1.4 3 0 6.5-2.5 8" />
                </svg> Proteger Meu Site Agora</a>
            <a href="mailto:comercial@front18.com?subject=Consultoria%20de%20Proteção%20LGPD" class="btn-lg btn-ghost"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg> Agendar Consultoria Grátis</a>
        </div>
        <div class="hero-proof">
            <span class="stars" style="display:inline-flex;gap:2px;color:var(--yellow)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
            </span>
            <span>4.9/5 — 127 sites protegidos</span>
            <span>|</span>
            <span style="display:inline-flex;align-items:center;gap:6px;"><span
                    style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--primary-accent);box-shadow:0 0 8px var(--primary-accent);"></span>
                99.9% uptime</span>
        </div>
    </section>

    <!-- DEADLINE STRIP -->
    <div class="deadline-strip">
        <div class="deadline-inner fade-in vis">
            <div class="deadline-item">
                <div class="num">R$ 50M</div>
                <div class="lbl">Multa máxima por infração</div>
            </div>
            <div class="deadline-item">
                <div class="num">24h</div>
                <div class="lbl">Prazo para denúncias (Art.28)</div>
            </div>
            <div class="deadline-item">
                <div class="num">15 dias</div>
                <div class="lbl">Prazo LGPD para titular (Art.18)</div>
            </div>
        </div>
    </div>

    <!-- RISCOS -->
    <section class="section" id="riscos">
        <div class="container text-center">
            <div class="sec-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg> YMYL — YOUR MONEY YOUR LIFE</div>
            <h2 class="sec-title">O que acontece se você <span style="color:var(--red)">NÃO</span> se adequar?</h2>
            <p class="sec-sub mx-auto">A ANPD e o Ministério Público já estão fiscalizando. Ignorar a lei não é opção.
            </p>
        </div>
        <div class="container">
            <div class="risk-grid fade-in vis">
                <div class="risk-card">
                    <div class="risk-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                            <path d="M12 18V6" />
                        </svg></div>
                    <h3>Multa de R$ 50 milhões</h3>
                    <p>Art. 52 da LGPD + Art. 29 da Lei FELCA. Multas por infração, aplicadas dia-a-dia.</p>
                </div>
                <div class="risk-card">
                    <div class="risk-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                        </svg></div>
                    <h3>Site bloqueado</h3>
                    <p>A Justiça pode determinar bloqueio global do domínio junto ao servidor local.</p>
                </div>
                <div class="risk-card">
                    <div class="risk-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                                d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
                        </svg></div>
                    <h3>Penalização Criminal</h3>
                    <p>Administradores podem responder penalmente por expor menores a risco.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SOLUÇÃO -->
    <section class="section sec-dark" id="solucao">
        <div class="container">
            <div class="sol-grid fade-in vis">
                <div>
                    <div class="sec-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" style="margin-right:6px">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg> A SOLUÇÃO</div>
                    <h2 class="sec-title">Biometria facial que protege seu negócio.</h2>
                    <p class="sec-sub" style="margin-bottom:24px">O Front18 é a catraca inteligente que bloqueia os
                        menores sem expor dados e sem perder clientes reais.</p>
                    <ul class="sol-list">
                        <li><span class="check">✓</span> <strong>Verificação facial neural</strong> — IA roda no
                            navegador</li>
                        <li><span class="check">✓</span> <strong>Conformidade tripla</strong> — Lei FELCA + Decreto +
                            LGPD</li>
                        <li><span class="check">✓</span> <strong>Dossiê forense</strong> — prova de diligência pronta
                        </li>
                    </ul>
                    <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
                        <a href="#planos" class="cta-btn cta-buy"><svg width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M8.5 14.5A2.5 2.5 0 0 0 11 12c-2.2-.6-3.8-2.6-4.5-5-.4-1.2-1.3-1.8-2.5-1.5a5.5 5.5 0 0 0 10 9c.7-2.2 0-4.6-2-6 1.4 3 0 6.5-2.5 8" />
                            </svg> Quero Proteger Meu Site</a>
                    </div>
                </div>
                <div class="sol-visual">
                    <div class="sol-face">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div class="sol-status">✓ ACESSO LIBERADO</div>
                    <div class="sol-status-sub">Biometria facial verificada • Idade: 18+</div>
                </div>
            </div>
        </div>
    </section>

    <!-- RECURSOS PREMIUM Z-PATTERN COM GSAP -->
    <section class="section" id="recursos" style="overflow:hidden;">
        <div class="container text-center" style="margin-bottom:80px;">
            <div class="sec-label">🔧 TECNOLOGIA DE PONTA</div>
            <h2 class="sec-title">Como a Engrenagem Funciona</h2>
            <p class="sec-sub mx-auto">Cada barreira do Front18 foi projetada para atuar silenciosamente, barrando sem atritos.</p>
        </div>
        
        <div class="container">
            <!-- GSAP Row 1 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                    <h3>Biometria Facial Neural</h3>
                    <p>O cérebro do Front18. Uma IA de ponta treinada com milhões de vértices faciais que atua em processamento contínuo local. Milissegundos de validação sem enviar a foto para a nuvem.</p><span class="feat-tag">INTELIGÊNCIA NATIVA</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card1.png" onerror="this.onerror=null; this.src='public/img/image/card1.png'; if(!this.complete)this.src='img/image/card1.png';" alt="Card 1">
                </div>
            </div>

            <!-- GSAP Row 2 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg></div>
                    <h3>Motor OCR Antifraudes</h3>
                    <p>Muitos tentam utilizar CPF de terceiros, mas o Front18 faz análise em tempo real do documento contra a face na câmera. Zero chance para violação de identidade.</p><span class="feat-tag">PRECISÃO MILIMÉTRICA</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card2.png" onerror="this.onerror=null; this.src='public/img/image/card2.png'; if(!this.complete)this.src='img/image/card2.png';" alt="Card 2">
                </div>
            </div>

            <!-- GSAP Row 3 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                    <h3>Age Gate Dinâmico</h3>
                    <p>A Lei FELCA acabou com botões inúteis de "Tenho +18 Anos". O Front18 instala a verificação forçada de liveness obrigando prova forense incontestável de maioridade.</p><span class="feat-tag">PADRÃO 15.211 RIGOROSO</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card3.png" onerror="this.onerror=null; this.src='public/img/image/card3.png'; if(!this.complete)this.src='img/image/card3.png';" alt="Card 3">
                </div>
            </div>
            
            <!-- GSAP Row 4 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <h3>Content Blur (Borrado Nativo)</h3>
                    <p>Imagens, Vídeos e Elementos ficam com distorção visual opaca imediatamente, seguindo o Decreto 12.880 para desincentivo à pirataria e a acessos rasteiros de menores.</p><span class="feat-tag">OFCUSCAÇÃO COMPLETA</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card4.png" onerror="this.onerror=null; this.src='public/img/image/card4.png'; if(!this.complete)this.src='img/image/card4.png';" alt="Card 4">
                </div>
            </div>

            <!-- GSAP Row 5 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
                    <h3>Monitoramento e Auditoria</h3>
                    <p>Paineis criptografados blindados para gerar logs probatórios e rastreios anônimos exigidos para apresentar uma defesa de chumbo caso a Justiça solicite.</p><span class="feat-tag">AUDITORIA BACKOFFICE</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card5.png" onerror="this.onerror=null; this.src='public/img/image/card5.png'; if(!this.complete)this.src='img/image/card5.png';" alt="Card 5">
                </div>
            </div>

            <!-- GSAP Row 6 -->
            <div class="gsap-row">
                <div class="gsap-text">
                    <div class="ico"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-accent)" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
                    <h3>Privacidade Compliance (10/10)</h3>
                    <p>Termos rigorosos e Cookies Zero. Sem persistência de rostos — garantindo que nem a LGPD e nem a ANPD respirem no calcanhar do seu negócio.</p><span class="feat-tag">LEGALIDADE BY DESIGN</span>
                </div>
                <div class="gsap-img">
                    <img src="/public/img/image/card6.png" onerror="this.onerror=null; this.src='public/img/image/card6.png'; if(!this.complete)this.src='img/image/card6.png';" alt="Card 6">
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING DYNAMICO PHP -->
    <section class="section sec-dark" id="planos">
        <div class="container text-center">
            <div class="sec-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" style="margin-right:6px">
                    <path d="M6 3h12l4 6-10 13L2 9Z" />
                    <path d="M11 3 8 9l4 13" />
                    <path d="M13 3l3 6-4 13" />
                </svg> PLANOS</div>
            <h2 class="sec-title">Quanto custa ficar em conformidade?</h2>
        </div>
        <div class="container">
            <div class="price-grid fade-in vis">
                <?php if (empty($planos)): ?>
                <div style="text-align:center;width:100%;color:red;grid-column: 1 / -1;">Não há planos configurados no
                    banco de dados.</div>
                <?php
else: ?>
                <?php foreach ($planos as $idx => $plan):
        $isFeatured = !empty($plan['is_featured']);
        $priceFmt = number_format($plan['price'], 0, ',', '.');
        $limitFmt = number_format($plan['max_requests_per_month'], 0, ',', '.');
?>
                <div class="p-card <?= $isFeatured ? 'hot' : ''?>">
                    <h3>
                        <?= htmlspecialchars($plan['name'])?>
                    </h3>
                    <div class="p-val">R$
                        <?= $priceFmt?><span>/mês</span>
                    </div>
                    <div class="p-limit">
                        <?= $limitFmt?> validações
                    </div>
                    <ul>
                        <li>
                            <?= htmlspecialchars($plan['max_domains'])?> domínio(s)
                        </li>
                        <?php if ($plan['allowed_level'] == 1): ?>
                        <li>Proteção Básica (Visual Blur)</li>
                        <?php
        elseif ($plan['allowed_level'] == 2): ?>
                        <li>Proteção Avançada</li>
                        <li>Auditoria Forense PDF</li>
                        <?php
        elseif ($plan['allowed_level'] >= 3): ?>
                        <li>Defesa Extrema (XOR Paranoia)</li>
                        <li>Auditoria Forense PDF</li>
                        <?php
        endif; ?>
                        <?php if (!empty($plan['has_seo_safe'])): ?>
                        <li>Motor Googlebot (SEO Safe)</li>
                        <?php
        endif; ?>
                        <?php if (!empty($plan['has_anti_scraping'])): ?>
                        <li>WAF Anti-Scraping / VPN</li>
                        <?php
        endif; ?>
                    </ul>
                    <a href="?route=register&plan_id=<?= $plan['id']?>"
                        class="p-btn <?= $isFeatured ? 'p-btn-hot' : 'p-btn-def'?>">
                        Começar Agora
                    </a>
                </div>
                <?php
    endforeach; ?>

                <div class="p-card">
                    <h3>Enterprise</h3>
                    <div class="p-val" style="font-size:24px">Sob Consulta</div>
                    <div class="p-limit">Ilimitado</div>
                    <ul>
                        <li>SLA Jurídico Imutável</li>
                        <li>Logs via Webhook/ERP</li>
                        <li>Equipe Jurídica Dedicada</li>
                    </ul>
                    <a href="mailto:comercial@front18.com?subject=Plano%20Enterprise" class="p-btn p-btn-def">Falar com Vendas</a>
                </div>
                <?php
endif; ?>
            </div>
        </div>
    </section>

    <!-- FAQ UNIFICADA -->
    <section class="section" id="faq">
        <div class="container text-center">
            <div class="sec-label"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" style="margin-right:6px">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg> FAQ</div>
            <h2 class="sec-title">Perguntas frequentes (Compliance Layer)</h2>
        </div>
        <div class="container">
            <div class="faq-list fade-in vis">
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">O que é a Lei FELCA
                        (15.211/2025)?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>É o marco legal que estabelece regras rigorosas de proteção a menores. Não cumprir sujeita
                            todo o site responsável a uma multa e bloqueios imediatos.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">A verificação acerta a
                        idade na primeira tentativa?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>A análise facial tem precisão média de ±3 anos — suficiente para bloquear menores
                            consistentemente. Para usuários claramente adultos, a liberação é quase instantânea.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">O Front18 me protege
                        legalmente contra processos?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>O Front18 é a sua maior evidência probatória perante a lei. Nós geramos um Dossiê Inalterável
                            provando o consentimento voluntário do visitante.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">Funciona em WordPress com
                        Cache Agressivo (LiteSpeed/Rocket)?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>Sim. A engine do Front18 possui Anti-Flickering assíncrono que não afeta seu cache, fura
                            restrições e aciona o modal da catraca imediatamente no navegador.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">Onde ficam as fotos do meu
                        usuário?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>Não armazenamos imagens, jamais! Operamos 100% via rede Edge AI. Toda análise passa na
                            máquina do cliente, voltando apenas a predição da idade em matemática criptografada.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">Meu site é E-Gaming /
                        Apostas. Preciso disso?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>Totalmente. A Lei engloba plataformas de gambling de forma severa. A antiga "autodeclaração"
                            (botão 'Tenho 18 anos') passa a ser ilegal em caso de fraude do menor.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">O Front18 afeta o SEO /
                        Indexação Google?<span class="arr">+</span></div>
                    <div class="faq-a">
                        <p>Jamais. Utilizamos uma lista oficial de IPs e DNS reverso do Googlebot (Sistema SEO Safe),
                            autorizando livre trânsito sem impacto para seu page load estruturado.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="final-cta">
        <div class="alert-box"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" style="vertical-align:-2px">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg> A fiscalização já começou. Não espere a notificação.</div>
        <h2>Proteja seu negócio digital completo.</h2>
        <div class="hero-ctas">
            <a href="#planos" class="btn-lg btn-danger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path
                        d="M8.5 14.5A2.5 2.5 0 0 0 11 12c-2.2-.6-3.8-2.6-4.5-5-.4-1.2-1.3-1.8-2.5-1.5a5.5 5.5 0 0 0 10 9c.7-2.2 0-4.6-2-6 1.4 3 0 6.5-2.5 8" />
                </svg> Ativar Proteção Agora</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-col">
                <div class="footer-brand">
                    <img src="public/img/logo.png" alt="Front18 Logo" style="height: 24px; object-fit: contain;">
                </div>
                <p>Infraestrutura B2B de verificação de idade por biometria facial complacente com a legislação.</p>
            </div>
            <div class="footer-col">
                <h4>Produto</h4>
                <a href="#planos">Planos</a>
                <a href="?route=login">Painel Admin</a>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="?route=privacy&ref=landing2">Privacidade</a>
                <a href="?route=terms&ref=landing2">Termos</a>
            </div>
        </div>
        <div class="footer-copy">
            © 2026 Front18 SaaS. Todos os direitos reservados.
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <script>
        // Scroll Menu Suave Nativo
        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var t = document.querySelector(this.getAttribute('href'));
                if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Efeitos Básicos
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) e.target.classList.add('vis'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(function (el) { obs.observe(el); });

        // -- ✨ EFEITOS GSAP PREMIUM (DEMOS STYLE) ✨ -- //
        gsap.registerPlugin(ScrollTrigger);

        // Z-Pattern Animations para a área de Tecnologia / Recursos
        // Varre cada linha da classe .gsap-row e alterna direções e parallax
        const rows = document.querySelectorAll('.gsap-row');
        rows.forEach((row, i) => {
            const isOdd = i % 2 !== 0; // Ex: 1, 3, 5 (Ímpares viram Row-Reverse)
            const text = row.querySelector('.gsap-text');
            const imgContent = row.querySelector('.gsap-img');

            if(isOdd) {
                row.classList.add('row-reverse'); 
            }

            // Anime Text Fade Up
            gsap.from(text, {
                scrollTrigger: {
                    trigger: row,
                    start: "top 85%", // Dispara qnd o topo chegar a 85% d tela
                    toggleActions: "play reverse play reverse"
                },
                y: 60,
                opacity: 0,
                duration: 1.2,
                ease: "power3.out"
            });

            // Anime Imagem vindo do Lado em 3D + Parallax
            gsap.from(imgContent, {
                scrollTrigger: {
                    trigger: row,
                    start: "top 85%",
                    toggleActions: "play reverse play reverse"
                },
                x: isOdd ? -150 : 150, // Vem da esq ou da dir
                rotationY: isOdd ? -15 : 15,
                rotationZ: isOdd ? -4 : 4,
                opacity: 0,
                duration: 1.6,
                ease: "expo.out"
            });
        });
        
        // Bonus Parallax pro Hero
        gsap.to(".hero h1", {
            y: 100,
            opacity: 0.2,
            ease: "none",
            scrollTrigger: {
                trigger: ".hero",
                start: "top top",
                end: "bottom top",
                scrub: true
            }
        });
    </script>
</body>

</html>