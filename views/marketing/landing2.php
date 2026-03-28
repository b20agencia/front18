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

    <!-- ESTILOS ANTIGRAVITY HERO (Importados e adaptados para Landing 2) -->
    <style>
        .hero-antigravity {
            position: relative;
            min-height: 90vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #050505; /* Deep dark */
            padding-top: 80px;
        }
        .hero-antigravity::before {
            content: '';
            position: absolute;
            inset: -50%;
            background: 
                radial-gradient(circle at 30% 70%, rgba(230, 0, 0, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 70% 30%, rgba(0, 221, 128, 0.1) 0%, transparent 40%);
            animation: antigravPulse 15s ease-in-out infinite alternate;
            z-index: 0;
            opacity: 0.8;
        }
        .isometric-grid {
            position: absolute;
            width: 200vw;
            height: 200vh;
            left: -50vw;
            top: -50vh;
            background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            transform: perspective(1000px) rotateX(60deg) rotateZ(45deg) translateY(-200px);
            z-index: 0;
            animation: gridMove 30s linear infinite;
        }
        @keyframes gridMove {
            0% { transform: perspective(1000px) rotateX(60deg) rotateZ(45deg) translateY(0); }
            100% { transform: perspective(1000px) rotateX(60deg) rotateZ(45deg) translateY(100px); }
        }
        @keyframes antigravPulse {
            0% { transform: scale(1) rotate(0deg); }
            100% { transform: scale(1.2) rotate(15deg); }
        }
        .glass-capsule {
            position: relative;
            z-index: 2;
            background: rgba(20, 20, 25, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
            max-width: 650px;
            transform-style: preserve-3d;
        }
        .btn-radiant {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            background: rgba(230, 0, 0, 0.1);
            border-radius: 12px;
            text-decoration: none;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 0 0 1px rgba(230,0,0,0.3);
            cursor: pointer;
        }
        .btn-radiant::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: conic-gradient(from 0deg, transparent 70%, #ff1a1a 100%);
            animation: neonSpin 2s linear infinite;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-radiant::after {
            content: '';
            position: absolute;
            inset: 2px;
            background: #110000;
            border-radius: 10px;
            z-index: -1;
            transition: background 0.3s;
        }
        .btn-radiant:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(230,0,0,0.3);
        }
        .btn-radiant:hover::before { opacity: 1; }
        .btn-radiant:hover::after { background: rgba(230,0,0,0.2); }
        @keyframes neonSpin { 100% { transform: rotate(360deg); } }

        /* Holograma 3D */
        .hologram-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
        }
        .hologram-card {
            width: 300px;
            height: 380px;
            background: linear-gradient(135deg, rgba(30,30,40,0.8), rgba(10,10,15,0.9));
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            position: relative;
            transform: rotateY(-15deg) rotateX(5deg);
            box-shadow: -20px 20px 50px rgba(0,0,0,0.5), inset 0 0 0 1px rgba(255,255,255,0.1);
            transform-style: preserve-3d;
            animation: floatObj 6s ease-in-out infinite;
            cursor: pointer;
        }
        .hologram-card:hover {
            animation-play-state: paused;
            transform: rotateY(0deg) rotateX(0deg) scale(1.05);
            transition: transform 0.4s ease;
        }
        .hologram-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent, rgba(230,0,0,0.1));
            border-radius: 20px;
            pointer-events: none;
        }
        .hologram-scan-line {
            position: absolute;
            left: 0; right: 0;
            height: 2px;
            background: var(--accent-red, #e60000);
            box-shadow: 0 0 20px 5px rgba(230, 0, 0, 0.4);
            animation: scanLine 2.5s ease-in-out infinite;
            z-index: 5;
            border-radius: 50%;
        }
        .hologram-content {
            position: absolute;
            inset: 20px;
            border: 1px dashed rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transform: translateZ(30px);
        }
        .face-mesh {
            width: 120px;
            height: 120px;
            border: 2px solid rgba(230, 0, 0, 0.8);
            border-radius: 50%;
            position: relative;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: rgba(0,0,0,0.5);
        }
        .face-mesh::before {
            content: '';
            position: absolute;
            inset: 10px;
            border: 1px dashed rgba(230, 0, 0, 0.5);
            border-radius: 50%;
            animation: spinMesh 10s linear infinite;
        }
        .face-mesh::after {
            content: '';
            position: absolute;
            inset: 20px;
            border: 1px solid rgba(230, 0, 0, 0.3);
            border-radius: 50%;
            animation: spinMesh 5s linear infinite reverse;
        }
        @keyframes spinMesh { 100% { transform: rotate(360deg); } }
        @keyframes scanLine {
            0% { top: 10%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }
        @keyframes floatObj {
            0%, 100% { transform: rotateY(-15deg) rotateX(5deg) translateY(0); }
            50% { transform: rotateY(-12deg) rotateX(8deg) translateY(-20px); }
        }
        @media (max-width: 900px) {
            .hero-antigravity { padding: 100px 15px 40px; flex-direction: column; min-height: auto; }
            .container-hero { grid-template-columns: 1fr !important; gap: 30px !important; }
            .glass-capsule { padding: 1.5rem; margin-bottom: 0; max-width: 100%; border-radius: 16px; }
            .glass-capsule h1 { font-size: clamp(2rem, 8vw, 2.5rem) !important; text-align: center !important; }
            .glass-capsule p { font-size: 1rem !important; text-align: center !important; margin-bottom: 2rem !important; }
            
            /* Center header elements */
            .glass-capsule .hero-eyebrow { margin: 0 auto 1.5rem auto !important; display: inline-flex !important; }
            .glass-capsule > div:first-child { width: 100%; text-align: center; }
            
            /* Center CTAs & stacking */
            .glass-capsule div[style*="display: flex; gap: 1rem"] { flex-direction: column; width: 100%; align-items: stretch !important; justify-content: center !important; text-align: center; }
            .glass-capsule .btn-radiant, .glass-capsule .btn { width: 100%; display: flex; justify-content: center; }
            .hero-proof { width: 100%; justify-content: center !important; text-align: center; flex-direction: column; gap: 8px !important; }
            
            /* Ajuste Holograma Mobile */
            .hologram-wrapper { height: auto; min-height: 350px; padding: 0; margin-top: 20px; perspective: none; }
            .hologram-card { width: 100%; max-width: 300px; height: 320px; margin: 0 auto; transform: none !important; animation: none; box-shadow: 0 10px 30px rgba(0,0,0,0.4); }
            /* Pílula Resolva em 30 Segs */
            .glass-capsule h1 span[style*="border-radius: 100px"] { font-size: 0.5em !important; padding: 6px 12px !important; margin: 15px auto 0 auto !important; display: flex !important; width: fit-content; }
        }
    </style>

    <!-- HERO ANTIGRAVITY -->
    <section class="hero-antigravity">
        <div class="isometric-grid"></div>
        <div class="container container-hero" style="position: relative; z-index: 2; width: 100%; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center; max-width: 1200px; margin: 0 auto;">
            
            <div class="glass-capsule fade-in">
                <div class="hero-eyebrow" style="display:inline-flex; align-items:center; border: 1px solid rgba(230,0,0,0.4); background: rgba(230,0,0,0.1); color: var(--accent-red, #ff1a1a); padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.1em; margin-bottom: 1.5rem; text-transform: uppercase;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none" style="margin-right:6px;">
                        <circle cx="12" cy="12" r="10" />
                    </svg> ATENÇÃO: SEU SITE ESTÁ IRREGULAR AGORA
                </div>
                
                <h1 style="font-size: clamp(2.3rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 2rem; color: #fff; text-align: left; text-shadow: 0 4px 20px rgba(0,0,0,0.5); font-weight: 800; letter-spacing: -0.02em;">
                    Seu site pode levar uma<br>
                    <span style="display: inline-block; background: linear-gradient(to right, #ff0f0f, #ff6b6b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.1em; transform: scale(1.02); padding: 5px 0;">multa de R$ 50 milhões</span><br>
                    <span style="display: inline-flex; align-items: center; gap: 8px; margin-top: 15px; font-size: 0.35em; font-weight: 700; color: #00dd80; border: 1px solid rgba(0, 221, 128, 0.3); background: rgba(0, 221, 128, 0.1); padding: 8px 16px; border-radius: 100px; box-shadow: 0 0 15px rgba(0,221,128,0.2); letter-spacing: 0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Resolva em 30 segundos
                    </span>
                </h1>
                
                <p style="font-size: 1.15rem; color: #b4b4cc; line-height: 1.6; margin-bottom: 2.5rem; text-align: left; max-width: 95%;">
                    A <strong style="color: #fff;">Lei FELCA (15.211/2025)</strong> exige verificação de idade rigorosa em sites com conteúdo restrito. 
                    O <strong style="color: #fff;">Decreto 12.880/2026</strong> regulamentou a necessidade com urgência. 
                    <br><br>
                    O Front18 atua como <strong style="color: var(--accent-red, #ff1a1a);">catraca inteligente zero-trust na borda.</strong>
                </p>
                
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: flex-start;">
                    <a href="#planos" class="btn-radiant">
                        <span style="position: relative; z-index: 2; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c-2.2-.6-3.8-2.6-4.5-5-.4-1.2-1.3-1.8-2.5-1.5a5.5 5.5 0 0 0 10 9c.7-2.2 0-4.6-2-6 1.4 3 0 6.5-2.5 8" />
                            </svg>
                            Blindar e Proteger Meu Site
                        </span>
                    </a>
                    <a href="mailto:comercial@front18.com?subject=Consultoria%20de%20Proteção%20LGPD" class="btn" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        Agendar Auditoria
                    </a>
                </div>
                
                <div class="hero-proof" style="margin-top: 2rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; font-size: 0.85rem; color: #a0a0b0; justify-content: flex-start;">
                    <span class="stars" style="display:inline-flex;gap:2px;color:var(--yellow, #F59E0B)">
                        <?php for($i=0; $i<5; $i++): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <?php endfor; ?>
                    </span>
                    <span>4.9/5 — 127 sites protegidos</span>
                    <span>|</span>
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--primary-accent, #00dd80);box-shadow:0 0 8px var(--primary-accent, #00dd80);"></span>
                        SLA 99.9%
                    </span>
                </div>
            </div>

            <div class="hologram-wrapper fade-in">
                <div class="hologram-card" title="Simulação Engine Front18">
                    <div class="hologram-scan-line"></div>
                    <div class="hologram-content">
                        <div class="face-mesh">
                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="rgba(230,0,0,0.8)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 20c.4-2.8 2.8-5 6-5h2c3.2 0 5.6 2.2 6 5"/><circle cx="12" cy="7" r="4"/><path d="M12 11v4"/><path d="M10 15h4"/>
                            </svg>
                        </div>
                        <div style="font-family: monospace; color: var(--accent-red, #ff1a1a); font-size: 0.9rem; letter-spacing: 0.1em; text-transform: uppercase;">
                            Liveness Shield
                        </div>
                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 8px;">
                            Borda Protegida SLA Total
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- DEADLINE STRIP ANTIGRAVITY -->
    <div class="deadline-strip" style="position: relative; background: rgba(5, 5, 8, 0.95); border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,10,10,0.15); padding: 5rem 0; overflow: hidden;">
        <!-- Scanline visual effect -->
        <div style="position: absolute; top: 0; left: -100%; width: 50%; height: 2px; background: linear-gradient(90deg, transparent, rgba(230,0,0,0.8), transparent); animation: scanLineAcross 4s ease-in-out infinite;"></div>
        
        <style>
            @keyframes scanLineAcross { 0% { left: -50%; } 100% { left: 150%; } }
            .deadline-antigrav-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
                position: relative;
                z-index: 2;
            }
            .deadline-card {
                background: rgba(30, 30, 35, 0.4);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 20px;
                padding: 3rem 1.5rem;
                text-align: center;
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s, border-color 0.4s;
                position: relative;
                overflow: hidden;
            }
            .deadline-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(230,0,0,0.15), inset 0 0 0 1px rgba(230,0,0,0.3);
                border-color: transparent;
            }
            .deadline-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 50% 0%, rgba(230,0,0,0.15) 0%, transparent 60%);
                opacity: 0;
                transition: opacity 0.4s;
            }
            .deadline-card:hover::before {
                opacity: 1;
            }
            .deadline-num {
                font-size: 3.8rem;
                font-weight: 900;
                color: transparent;
                -webkit-text-stroke: 1.5px rgba(230, 0, 0, 0.6);
                line-height: 1;
                margin-bottom: 1.2rem;
                position: relative;
                text-shadow: 0 0 20px rgba(230,0,0,0.1);
                transition: all 0.4s ease;
            }
            /* Fill animation on hover */
            .deadline-card:hover .deadline-num {
                background: linear-gradient(135deg, #ff4c4c, #cc0000);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                -webkit-text-stroke: 0;
                text-shadow: 0 5px 25px rgba(230,0,0,0.4);
            }
            .deadline-lbl {
                font-size: 1.05rem;
                color: #a0a0b0;
                font-weight: 600;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                position: relative;
                z-index: 2;
                transition: color 0.3s;
            }
            .deadline-card:hover .deadline-lbl {
                color: #fff;
            }
            @media (max-width: 900px) {
                .deadline-antigrav-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            }
            @media (max-width: 768px) {
                .deadline-strip { padding: 3rem 0 !important; }
                .deadline-card { padding: 2rem 1.5rem; }
                .deadline-num { font-size: clamp(2.5rem, 12vw, 3.5rem) !important; }
            }
        </style>

        <div class="deadline-antigrav-grid fade-in vis">
            <div class="deadline-card">
                <div class="deadline-num">R$ 50M</div>
                <div class="deadline-lbl">Multa máxima por infração</div>
            </div>
            <div class="deadline-card">
                <div class="deadline-num">24h</div>
                <div class="deadline-lbl">Prazo para denúncias (Art.28)</div>
            </div>
            <div class="deadline-card">
                <div class="deadline-num">15 dias</div>
                <div class="deadline-lbl">Prazo LGPD para titular (Art.18)</div>
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

    <!-- SOLUÇÃO ANTIGRAVITY -->
    <section class="section sec-dark" id="solucao" style="position:relative; overflow:hidden;">
        <!-- Fundo de poeira cósmica / flares subtis -->
        <div style="position:absolute; inset:0; background: radial-gradient(circle at 80% 50%, rgba(0,221,128,0.05) 0%, transparent 60%); z-index:0;"></div>
        
        <style>
            .sol-antigrav-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                align-items: center;
                position: relative;
                z-index: 2;
            }
            .sol-features li {
                list-style: none;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                font-size: 1.1rem;
                color: #b4b4b4;
            }
            .sol-features .feat-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                background: rgba(0, 221, 128, 0.1);
                border: 1px solid rgba(0, 221, 128, 0.4);
                border-radius: 50%;
                color: #00dd80;
                flex-shrink: 0;
                box-shadow: 0 0 10px rgba(0,221,128,0.2);
            }
            /* O Radar Simulator */
            .radar-glass-panel {
                background: rgba(15, 15, 20, 0.5);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 24px;
                padding: 3rem;
                position: relative;
                box-shadow: 0 30px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                min-height: 400px;
                transition: transform 0.5s ease;
            }
            .radar-glass-panel:hover {
                transform: rotateY(0deg) rotateX(0deg) scale(1.02) !important;
                border-color: rgba(0, 221, 128, 0.3);
            }
            .radar-circle {
                width: 160px;
                height: 160px;
                border-radius: 50%;
                border: 2px solid rgba(0, 221, 128, 0.2);
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 2rem;
            }
            .radar-circle::after {
                content: '';
                position: absolute;
                inset: -20px;
                border: 1px dashed rgba(0, 221, 128, 0.4);
                border-radius: 50%;
                animation: spinSlow 15s linear infinite;
            }
            .radar-scan-arm {
                position: absolute;
                top: 50%; left: 50%;
                width: 50%; height: 2px;
                background: linear-gradient(90deg, transparent, #00dd80);
                transform-origin: 0 50%;
                animation: scanRadar 3s linear infinite;
                z-index: 10;
            }
            .radar-scan-arm::after {
                content: '';
                position: absolute;
                right: 0; top: -20px;
                width: 40px; height: 40px;
                background: radial-gradient(circle at 100% 50%, rgba(0,221,128,0.8), transparent);
                opacity: 0.5;
            }
            @keyframes scanRadar { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            @keyframes spinSlow { 100% { transform: rotate(360deg); } }
            
            .auth-status {
                background: rgba(0, 221, 128, 0.1);
                border: 1px solid rgba(0, 221, 128, 0.3);
                color: #00dd80;
                padding: 10px 24px;
                border-radius: 100px;
                font-weight: 700;
                letter-spacing: 2px;
                font-size: 0.9rem;
                animation: pulseAuth 2s infinite;
            }
            @keyframes pulseAuth {
                0%, 100% { box-shadow: 0 0 10px rgba(0,221,128,0); }
                50% { box-shadow: 0 0 20px rgba(0,221,128,0.4); }
            }
            
            /* Add specificity to override original red button if needed, but we inline it below */
            .btn-neon-green {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 1rem 2rem;
                font-size: 1.1rem;
                font-weight: 700;
                color: #00dd80;
                background: rgba(0, 221, 128, 0.1);
                border-radius: 12px;
                text-decoration: none;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 0 0 1px rgba(0,221,128,0.3);
                cursor: pointer;
            }
            .btn-neon-green::before {
                content: '';
                position: absolute;
                inset: -2px;
                background: conic-gradient(from 0deg, transparent 70%, #00dd80 100%);
                animation: neonSpin 2s linear infinite;
                z-index: -1;
                opacity: 0;
                transition: opacity 0.3s;
            }
            .btn-neon-green::after {
                content: '';
                position: absolute;
                inset: 2px;
                background: #05110c;
                border-radius: 10px;
                z-index: -1;
                transition: background 0.3s;
            }
            .btn-neon-green:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(0,221,128,0.3);
            }
            .btn-neon-green:hover::before { opacity: 1; }
            .btn-neon-green:hover::after { background: rgba(0,221,128,0.15); }
            
            @media (max-width: 900px) {
                .sol-antigrav-grid { grid-template-columns: 1fr; gap: 3rem; text-align: center; }
                .sol-features li { flex-direction: column; align-items: center; text-align: center; gap: 0.5rem; }
                .sol-antigrav-grid .hero-eyebrow { margin-left: auto !important; margin-right: auto !important; display: inline-flex !important; }
                .sol-antigrav-grid h2 { text-align: center !important; font-size: clamp(2rem, 7vw, 2.5rem) !important; }
                .sol-antigrav-grid p { text-align: center !important; }
                .sol-antigrav-grid div[style*="display:flex; gap:12px"] { justify-content: center !important; width: 100%; }
                .radar-glass-panel { min-height: auto; padding: 2.5rem 1rem; transform: none !important; margin: 0 auto; max-width: 100%; }
                .radar-glass-panel:hover { transform: none !important; }
            }
        </style>

        <div class="container" style="padding-top:4rem; padding-bottom:4rem;">
            <div class="sol-antigrav-grid fade-in vis">
                <div>
                    <div class="hero-eyebrow" style="display:inline-flex; align-items:center; border: 1px solid rgba(0,221,128,0.3); background: rgba(0,221,128,0.1); color: #00dd80; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.1em; margin-bottom: 1.5rem; text-transform: uppercase;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg> A SOLUÇÃO
                    </div>
                    <h2 style="font-size: clamp(2rem, 3.5vw, 3rem); line-height: 1.1; margin-bottom: 1.5rem; color: #fff; font-weight: 800; letter-spacing: -0.02em;">Biometria facial que protege seu negócio.</h2>
                    <p style="font-size: 1.15rem; color: #a0a0b0; line-height: 1.6; margin-bottom: 2.5rem;">O Front18 é a <strong>catraca inteligente zero-trust</strong> que bloqueia os infratores sem expor dados e sem barrar os clientes reais.</p>
                    
                    <ul class="sol-features" style="padding:0;">
                        <li>
                            <div class="feat-icon">✓</div>
                            <div><strong style="color: #fff;">Verificação facial neural</strong> — IA roda 100% silenciosa no navegador do usuário.</div>
                        </li>
                        <li>
                            <div class="feat-icon">✓</div>
                            <div><strong style="color: #fff;">Conformidade tripla máxima</strong> — Cobre as exigências da Lei FELCA, do Decreto e da LGPD.</div>
                        </li>
                        <li>
                            <div class="feat-icon">✓</div>
                            <div><strong style="color: #fff;">Dossiê forense</strong> — Cria prova de diligência pronta e auditável para eventuais denúncias.</div>
                        </li>
                    </ul>
                    
                    <div style="margin-top:2.5rem; display:flex; gap:12px; flex-wrap:wrap">
                        <a href="#planos" class="btn-neon-green">
                            <span style="position: relative; z-index: 2; display: flex; align-items: center; gap: 8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Quero Proteger Meu Site
                            </span>
                        </a>
                    </div>
                </div>
                
                <div class="sol-visual-container" style="perspective:1000px;">
                    <div class="radar-glass-panel" style="transform: rotateY(-10deg) rotateX(5deg);">
                        
                        <div class="radar-circle">
                            <div class="radar-scan-arm"></div>
                            
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#00dd80" stroke-width="1.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        
                        <div class="auth-status">✓ ACESSO LIBERADO</div>
                        <div style="color: rgba(255,255,255,0.4); font-size: 0.85rem; margin-top: 1.2rem; font-family: monospace;">
                            Biometria facial verificada • Idade: 18+
                        </div>
                        
                        <!-- Mini console flying texts -->
                        <div style="position: absolute; bottom: 20px; left: 20px; opacity: 0.3; font-family: monospace; font-size: 0.65rem; color: #00dd80;">
                            > front18.liveness_check()...<br>
                            > mesh_capture: OK [0.12ms]<br>
                            > neural_vectors: MATCH (99.8%)
                        </div>
                    </div>
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