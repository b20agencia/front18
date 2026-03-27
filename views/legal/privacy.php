<?php $ref = $_GET['ref'] ?? 'landing2'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade — Front18 Protocolo de Conformidade</title>
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
        
        /* Box Components */
        .important-box { background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); padding: 20px; border-radius: 8px; margin: 24px 0; }
        .important-box strong { color: var(--red); display: block; margin-bottom: 8px; font-size: 14px; }
        .consent-box { background: rgba(0, 221, 128, 0.05); border: 1px solid rgba(0, 221, 128, 0.2); padding: 20px; border-radius: 8px; margin: 24px 0; }
        .consent-box strong { color: var(--primary-accent); display: block; margin-bottom: 8px; font-size: 14px; }
        
        /* Table Formatting */
        .legal-table { width: 100%; border-collapse: collapse; margin: 24px 0; background: rgba(255,255,255,0.02); border-radius: 8px; overflow: hidden; }
        .legal-table th, .legal-table td { border: 1px solid rgba(255,255,255,0.1); padding: 14px 16px; text-align: left; font-size: 14px; }
        .legal-table th { background: rgba(0,221,128,0.1); color: var(--primary-accent); font-weight: 700; white-space: nowrap; }
        .legal-table td { color: #A0AEC0; }
        
        .legal-footer { margin-top: 50px; text-align: center; font-size: 13px; color: var(--dim); padding-top: 30px; border-top: 1px solid var(--border); }
        .legal-footer a { color: var(--primary-accent); text-decoration: none; margin: 0 10px; }
        .legal-footer a:hover { text-decoration: underline; }
        @media(max-width: 768px) { .legal-container { padding: 30px 20px; margin: 100px 20px; } .legal-table { display: block; overflow-x: auto; } }
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
        <h1>Política de Privacidade</h1>
        <p>Última atualização: 23 de março de 2026</p>
    </div>

    <div class="legal-content">
        <h2><span class="num">1</span> Identificação do Controlador (Art.9 III)</h2>
        <p><strong>Controlador de Dados</strong><br>Razão Social Ltda<br>CNPJ: 00.000.000/0001-00<br>Site: https://meusite.com.br</p>
        <p><strong>Encarregado de Dados (DPO) — Art.41</strong><br>Nome do Encarregado<br><span style="color:var(--primary-accent)">📧 dpo@seudominio.com.br</span><br>📞 (11) 0000-0000</p>

        <h2><span class="num">2</span> Dados Pessoais que Coletamos (Art.9 I)</h2>
        <p>O Meu Site coleta o mínimo de dados necessários para cumprir a Lei 15.211/2025 (proteção de crianças e adolescentes em ambientes digitais). Abaixo detalhamos cada dado:</p>
        
        <table class="legal-table">
            <thead>
                <tr>
                    <th>Dado</th>
                    <th>Finalidade</th>
                    <th>Base Legal</th>
                    <th>Retenção</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Endereço IP (hash)</td>
                    <td>Segurança e anti-fraude</td>
                    <td>Art.7 IX — Legítimo interesse</td>
                    <td>Hash irreversível</td>
                </tr>
                <tr>
                    <td>Foto de documento / Biometria</td>
                    <td>Verificação de idade</td>
                    <td>Art.11 I — Consentimento + Art.7 II — Obrigação legal</td>
                    <td>Eliminada imediatamente</td>
                </tr>
                <tr>
                    <td>Data de nascimento</td>
                    <td>Cálculo de idade autônomo</td>
                    <td>Art.7 II — Obrigação legal (Lei 15.211)</td>
                    <td>Não armazenada</td>
                </tr>
                <tr>
                    <td>Faixa etária</td>
                    <td>Controle de acesso</td>
                    <td>Dado anonimizado (Art.12)</td>
                    <td>Duração da sessão</td>
                </tr>
                <tr>
                    <td>Token de sessão criptográfica</td>
                    <td>Manter estado da verificação</td>
                    <td>Art.7 IX — Legítimo interesse</td>
                    <td>Até 30 dias</td>
                </tr>
                <tr>
                    <td>Dados de denúncia</td>
                    <td>Canal de denúncias (Lei 15.211 Art.28)</td>
                    <td>Art.7 II — Obrigação legal</td>
                    <td>Conforme prazo legal</td>
                </tr>
            </tbody>
        </table>
        
        <div class="consent-box">
            <strong style="display:flex;align-items:center;gap:6px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Privacy by Design (Art.46 §2):</strong>
            <p style="color:#cbd5e1; margin-bottom:0;">O sistema foi projetado desde a concepção para coletar o mínimo necessário. A foto do documento d biometria facial é processada exclusivamente em memória local e eliminada instantaneamente — nunca salva em disco ou rede externa. Apenas a matemática final de idade (ex: "18+") é retida sob Hash.</p>
        </div>

        <h2><span class="num">3</span> Finalidades do Tratamento (Art.6 I, Art.9 I)</h2>
        <p>Os dados pessoais são tratados exclusivamente para:</p>
        <ul>
            <li><strong>Verificação de idade</strong> — Cumprimento da Lei 15.211/2025 (ECA), que obriga sites com conteúdo restrito a verificar a maturidade real dos usuários.</li>
            <li><strong>Segurança</strong> — Prevenção de fraudes e ataques maliciosos (rate limiting, bloqueio de IPs em proxy/vpn agressivos).</li>
            <li><strong>Auditoria e Canal de Denúncias</strong> — Geração de logs restritos para fins judiciais (Dec 12.880, Art.47).</li>
        </ul>
        
        <div class="important-box">
            <strong style="display:flex;align-items:center;gap:6px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg> O que NÃO fazemos:</strong>
            <ul style="margin:0; color:#e2e8f0; font-size: 14px;">
                <li>Não criamos perfis comportamentais de usuários.</li>
                <li>Não compartilhamos dados com terceiros ou anunciantes.</li>
                <li>Não realizamos rastreamento (cross-site tracking profiling).</li>
                <li>Não armazenamos, não vendemos e não mantemos cópias fotográficas da sua identidade/biometria.</li>
            </ul>
        </div>

        <h2><span class="num">4</span> Bases Legais para Tratamento (LGPD - Art.7)</h2>
        <ul>
            <li><strong>Obrigação legal (Art.7 II)</strong> — Verificação de idade conforme exigido pela Lei Federal 15.211/2025.</li>
            <li><strong>Legítimo interesse (Art.7 IX)</strong> — Segurança estrutural do site e prova de cumprimento técnico judicial.</li>
            <li><strong>Consentimento (Art.7 I / Art.11 I)</strong> — Para processamento sensível transitório, mediante consentimento expresso, específico e destacado de tela.</li>
        </ul>

        <h2><span class="num">5</span> Compartilhamento de Dados (Art.9 V)</h2>
        <p>Seus poucos vestígios de dados estatísticos/Hashs jamais são compartilhados irrestritamente com terceiros, limitando-se unicamente a:</p>
        <ul>
            <li><strong>Obrigação legal</strong> — Quando forçados de forma transparente via mandato por autoridade judicial.</li>
            <li><strong>Denúncias criminais</strong> — Comunicação explícita ao Conselho Nacional de Telecomunicações e/ou autoridade policial judiciária (Ex: Polícia Federal) em fraudes severas contra o sistema as a Service.</li>
        </ul>

        <h2><span class="num">6</span> Seus Direitos como Titular (Art.18)</h2>
        <p>Você tem os seguintes direitos consolidados e assegurados em território brasileiro (LGPD):</p>
        <ul style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
            <li><strong>Confirmação</strong> e <strong>Acesso</strong></li>
            <li><strong>Correção</strong> (incompletos/inexatos)</li>
            <li><strong>Portabilidade</strong> (transferência segura)</li>
            <li><strong>Informação</strong> e <strong>Revogação</strong> (Consentimento)</li>
        </ul>
        <div class="consent-box" style="padding:15px; margin-top:20px;">
            <strong>Como exercer:</strong> Acesse nossa página de solicitações ou envie um e-mail para <span style="color:#fff">dpo@seudominio.com.br</span>. Responderemos em até 15 dias corridos (Art.19).
        </div>

        <h2><span class="num">7</span> Segurança da Informação (Art.46)</h2>
        <p>Nosso projeto e a Engine <strong>Front18 Shield</strong> empregam arquitetura state-of-the-art contra vazamentos, incluindo criptografia SHA-256 irreversível (Salting), exclusão instantânea RAM de pixels, HTTP Strict Transport Security (HSTS) e CSP Restrito para blindar ataques de terceiros na borda.</p>

        <h2><span class="num">8</span> Cookies e Semântica de Edge Device</h2>
        <p>Diferente de sistemas de Ad-tech que implantam dezenas de rastreadores, nosso sistema é contido no navegador para finalidades restritas de Segurança e Legislação:</p>
        <table class="legal-table">
            <thead>
                <tr>
                    <th>Cookie</th>
                    <th>Tipo</th>
                    <th>Finalidade</th>
                    <th>Duração</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>felca_session_f18</td>
                    <td>Essencial</td>
                    <td>Manter estado aprovatório do Age Gate +18</td>
                    <td>Sessão / até 30 dias</td>
                </tr>
                <tr>
                    <td>felca_consent</td>
                    <td>Essencial</td>
                    <td>Lembrar preferência LGPD Legal</td>
                    <td>365 dias</td>
                </tr>
            </tbody>
        </table>
        <p style="font-size:13px; color:var(--dim);">Cookies não essenciais (marketing, third-party) só disparam <strong>após</strong> você passar na verificação e conceder o banner LGPD explícito.</p>

        <h2><span class="num">9</span> Proteção Fundamental ao Menor (LGPD Art.14)</h2>
        <p>A natureza do nosso material web é de Classificação 18+. O mecanismo e o site barram o acesso, desligam scripts de rastreamento antes da aprovação legal da catraca e garantem a política estrita de desvinculação absoluta de perfilamento comportamental de menores detectados pelos logarítmos do Front18.</p>

        <h2><span class="num">10</span> Retenção e Diluição Legal de Dados</h2>
        <p>A IA do mecanismo destrói a representação imagética de sua biometria em menos de <strong style="color:#fff">0.9 segundos</strong>.</p>
        <p>Os Hashs de sessão de provação caem automaticamente em 30 dias temporários. Os metadados de auditorias judiciais são guardados unicamente pelo prazo regulatório para eventuais contestações cíveis ou perante o Ministério Público do Brasil.</p>
        
        <h2><span class="num">11</span> Alterações e Contato</h2>
        <p>Recomendamos a consulta periódica devido às adequações rápidas da Lei FELCA vigente de 2025/2026. Em caso de dúvidas complexas, a ANPD do governo federal está apta a dirimir impasses junto aos cidadãos via www.gov.br/anpd.</p>

        <p style="margin-top:20px;"><strong>Contato imediato DPO:</strong></p>
        <p style="font-weight:700; color:var(--primary-accent)">📧 dpo@seudominio.com.br</p>

    </div>

    <div class="legal-footer">
        <p style="margin-bottom: 20px;">🛡️ Protegido pelo mecanismo Neural do <strong>Front18 Shield</strong> — Garantia Legal de Compliance de Dados.</p>
        <div style="display:flex; justify-content:center; flex-wrap:wrap; gap: 10px;">
            <a href="?route=privacy&ref=<?= htmlspecialchars($ref) ?>" style="color:#fff;font-weight:bold;">Política de Privacidade</a>
            <span style="color:var(--border)">•</span>
            <a href="?route=terms&ref=<?= htmlspecialchars($ref) ?>">Termos de Uso</a>
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
