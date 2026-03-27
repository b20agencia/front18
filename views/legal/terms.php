<?php $ref = $_GET['ref'] ?? 'landing2'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso — Front18 Protocolo de Conformidade</title>
    <link rel="icon" type="image/png" href="/public/img/favicon.png">
    <link rel="stylesheet" href="/public/css/new.css?v=<?= time() ?>">
    <style>
        body { background: var(--bg-canvas); }
        .legal-container { max-width: 800px; margin: 120px auto 100px; padding: 50px 60px; background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 15px 50px rgba(0,0,0,0.5); }
        .legal-header { text-align: center; border-bottom: 1px solid var(--border); padding-bottom: 30px; margin-bottom: 30px; }
        .legal-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; color: var(--text-main); }
        .legal-header p { color: var(--text-muted); font-size: 14px; }
        .legal-content h2 { font-size: 18px; font-weight: 700; color: var(--primary-accent); margin: 40px 0 16px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,221,128,0.15); display: flex; align-items: center; gap: 8px; }
        .legal-content h2 span.num { background: rgba(0,221,128,0.1); color: var(--primary-accent); width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px; }
        .legal-content p { color: #A0AEC0; font-size: 15px; margin-bottom: 16px; line-height: 1.8; }
        .legal-content ul { margin-bottom: 20px; padding-left: 24px; }
        .legal-content li { color: #A0AEC0; font-size: 15px; margin-bottom: 8px; line-height: 1.6; }
        .legal-content li::marker { color: var(--primary-accent); }
        .important-box { background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); padding: 20px; border-radius: 8px; margin: 24px 0; }
        .important-box strong { color: var(--red); display: block; margin-bottom: 8px; font-size: 14px; }
        .consent-box { background: rgba(0, 221, 128, 0.05); border: 1px solid rgba(0, 221, 128, 0.2); padding: 20px; border-radius: 8px; margin: 24px 0; }
        .consent-box strong { color: var(--primary-accent); display: block; margin-bottom: 8px; font-size: 14px; }
        .legal-footer { margin-top: 50px; text-align: center; font-size: 13px; color: var(--dim); padding-top: 30px; border-top: 1px solid var(--border); }
        .legal-footer a { color: var(--primary-accent); text-decoration: none; margin: 0 10px; }
        .legal-footer a:hover { text-decoration: underline; }
        @media(max-width: 768px) { .legal-container { padding: 30px 20px; margin: 100px 20px; } }
    </style>
</head>
<body>

<!-- NAVBAR BÁSICA LIMPADA PARA O DEPARTAMENTO LEGAL -->
<nav class="nav">
    <div class="nav-inner" style="justify-content: center;">
        <div class="nav-brand">
            <a href="?route=<?= htmlspecialchars($ref) ?>" style="display:flex; align-items:center;">
                <img src="public/img/logo.png" alt="Front18 Logo" style="height: 24px; object-fit: contain;">
                <span style="font-size:12px; font-weight:400; color:var(--muted); margin-left: 10px; border-left: 1px solid var(--border); padding-left: 10px;">Legal & Compliance Dept.</span>
            </a>
        </div>
    </div>
</nav>

<div class="legal-container fade-in vis">
    <div class="legal-header">
        <h1>Termos de Uso</h1>
        <p>Última atualização: 23 de março de 2026</p>
    </div>

    <div class="legal-content">
        <h2><span class="num">1</span> Aceitação dos Termos</h2>
        <p>Ao acessar e utilizar o site Meu Site ("Site"), operado por Razão Social Ltda, você concorda com estes Termos de Uso e com nossa Política de Privacidade.</p>

        <h2><span class="num">2</span> Restrição de Idade</h2>
        <div class="important-box">
            <strong>⚠️ Classificação Indicativa: 18+</strong>
            <p style="color: #cbd5e1; margin-bottom: 0;">Este site contém conteúdo restrito a maiores de 18 anos, conforme a Lei 15.211/2025 (Estatuto Digital da Criança e do Adolescente) e o Decreto 12.880/2026.</p>
        </div>
        <p>Para acessar o conteúdo, é obrigatória a verificação de idade por meio de métodos que não constituam autodeclaração (Lei 15.211, Art.9 §1). Os métodos disponíveis incluem:</p>
        <ul>
            <li>Verificação por cartão de crédito (comprovação de capacidade civil plena)</li>
            <li>Verificação por documento de identidade com OCR/Biometria Facial Neural</li>
        </ul>

        <h2><span class="num">3</span> Consentimento para Tratamento de Dados (LGPD Art.8)</h2>
        <div class="consent-box">
            <strong>📋 Cláusula Destacada de Consentimento (Art.8 §1):</strong>
            <p style="color: #cbd5e1; margin-bottom: 0;">Ao submeter sua biometria facial ou documento de identidade para verificação de idade, você consente expressamente com o tratamento temporário de seus dados sensíveis, exclusivamente para a finalidade de extração e cálculo de sua faixa etária.</p>
        </div>
        <p>Este consentimento refere-se a uma finalidade específica e determinada (Art.8 §4). A imagem biométrica é processada em memória local (Rede Edge) e eliminada imediatamente após o processamento — nunca é armazenada em disco ou banco de dados.</p>
        <p>Você pode revogar este consentimento a qualquer momento (Art.8 §5), o que resultará na eliminação de sua sessão criptografada de verificação.</p>

        <h2><span class="num">4</span> Obrigações do Usuário</h2>
        <p>Ao utilizar o Site, você se compromete a:</p>
        <ul>
            <li>Fornecer informações verdadeiras durante a verificação de idade.</li>
            <li>Não tentar burlar os mecanismos neurais de verificação e liveness.</li>
            <li>Não permitir que menores de 18 anos acessem o conteúdo por meio de sua conexão/sessão já aprovada.</li>
            <li>Utilizar o canal de denúncias de forma responsável e de boa-fé.</li>
            <li>Respeitar o tempo de uso recomendado pelos sistemas de prevenção.</li>
        </ul>

        <h2><span class="num">5</span> Supervisão Parental</h2>
        <p>Em conformidade com a Lei 15.211 (Arts. 17-18) e a LGPD (Art.14), oferecemos e apoiamos ferramentas de supervisão parental que permitem a pais e responsáveis:</p>
        <ul>
            <li>Monitorar e restringir o acesso de seus dependentes.</li>
            <li>Configurar limites de tempo de uso diários e noturnos.</li>
            <li>Bloquear categoricamente o acesso a este formato de site.</li>
        </ul>

        <h2><span class="num">6</span> Canal de Denúncias</h2>
        <p>Conforme exigido pelos Arts. 28 e 29 da Lei 15.211/2025 e Arts. 41-46 do Decreto 12.880/2026, disponibilizamos canal de denúncias acessível, identificado e de fácil uso para reportar:</p>
        <ul>
            <li>Conteúdo ilegal, subversivo ou impróprio;</li>
            <li>Violação de direitos de crianças e adolescentes;</li>
            <li>Exploração sexual, abuso e falsidade ideológica;</li>
            <li>Falhas mecânicas/lógicas no sistema de verificação de idade (Front18 Shield).</li>
        </ul>

        <h2><span class="num">7</span> Propriedade Intelectual</h2>
        <p>O <strong>Front18 Shield</strong> (Arquitetura) é um sistema de conformidade legal de terceiros as a Service. O conteúdo do site publicador é de propriedade de "Razão Social Ltda" e protegido pela legislação vigente de direitos autorais de forma autônoma.</p>

        <h2><span class="num">8</span> Limitação de Responsabilidade</h2>
        <p>O sistema Front18 Shield emprega as melhores práticas técnicas e judiciais disponíveis hoje no mundo (Biometria Facial Híbrida e Inteligência Artificial Edge) para verificação de idade, mas pontuamos que responsáveis devem utilizar ativamente as ferramentas primárias de supervisão parental atreladas aos chips telefônicos e Sistemas Operacionais (iOS/Android) como camada prioritária de proteção às famílias.</p>

        <h2><span class="num">9</span> Legislação Aplicável</h2>
        <p>Estes Termos são integralmente regidos pela legislação e pelas Cortes do Brasil, especialmente pelas frentes abaixo:</p>
        <ul>
            <li><strong>Lei 13.709/2018</strong> — LGPD (Lei Geral de Proteção de Dados)</li>
            <li><strong>Lei 15.211/2025</strong> — FELCA (Proteção de Crianças em Ambientes Digitais e IA)</li>
            <li><strong>Decreto 12.880/2026</strong> — Regulamentação de Conformidade Cívil da Lei 15.211</li>
            <li><strong>Lei 8.069/1990</strong> — ECA (Estatuto da Criança e do Adolescente)</li>
            <li><strong>Lei 12.965/2014</strong> — Marco Civil da Internet</li>
        </ul>

        <h2><span class="num">10</span> Alterações</h2>
        <p>Reservamo-nos o direito inarredável de alterar estes Termos devido a flexão das leis nacionais. Alterações significativas operando na Lei FELCA ou LGPD serão comunicadas com destaque no site (Splash Screen). O seu recém uso continuado após alterações constitui prova imaterial de aceitação dos novos termos de consentimento.</p>

        <h2><span class="num">11</span> Contato Formal (DPO)</h2>
        <p>Para dúvidas jurídicas sobre estes Termos de Conduta ou sobre a malha de tratamento criptográfico de dados de trânsito:</p>
        <p style="font-weight:700; color:var(--primary-accent)">📧 dpo@front18.com.br</p>
    </div>

    <div class="legal-footer">
        <p style="margin-bottom: 20px;">🛡️ Protegido pelo mecanismo Neural do <strong>Front18 Shield</strong> — Garantia Legal de Conformidade (LGPD & Lei FELCA).</p>
        <div style="display:flex; justify-content:center; flex-wrap:wrap; gap: 10px;">
            <a href="?route=privacy&ref=<?= htmlspecialchars($ref) ?>">Política de Privacidade</a>
            <span style="color:var(--border)">•</span>
            <a href="?route=terms&ref=<?= htmlspecialchars($ref) ?>" style="color:#fff;font-weight:bold;">Termos de Uso</a>
            <span style="color:var(--border)">•</span>
            <a href="?route=<?= htmlspecialchars($ref) ?>">Página Inicial (Voltar)</a>
        </div>
    </div>
</div>

<script>
var obs = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) { if (e.isIntersecting) e.target.classList.add('vis'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(function(el) { obs.observe(el); });
</script>
</body>
</html>
