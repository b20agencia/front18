<?php
/**
 * Arquivo: track.php | Endpoint de Tracking de Logs Biométricos e Bloqueios
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
/**
 * Front18 B2B API Engine - O Coração do SaaS
 * Ingestão de Logs, WAF e Autoridade Cross-Domain via CORS.
 */

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Core/SessionManager.php';
require_once __DIR__ . '/../../src/Core/Crypto.php';

// Ative para debugar no PHP Error Log
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ========================================================
// 1. COMPLIANCE JURÍDICO & CORS ESTrito (Validação B2B)
// ========================================================
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_X_Front18_TOKEN'] ?? $_GET['key'] ?? ''; 
$action = $_GET['action'] ?? 'content';

// Headers Universais de CORS e Autoridade
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY, X-Front18-Token, Cache-Control, Pragma, Accept');
if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
header('Access-Control-Allow-Credentials: true'); // Permite cookies cross-domain (Third-party)

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Retorno Rápido para Preflights do Navegador
    http_response_code(200);
    exit;
}

Database::setup();
$pdo = Database::getConnection();

// Trava WAF / Validador de Licença Ativa
$clientId = 0;
$domainId = 0;

if (!$apiKey) {
    // Bloqueio WAF: Scraper ou Integração malfeita
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipMasked = $ip;
    if (strpos($ip, ':') !== false) {
        $ipParts = explode(':', $ip);
        if (count($ipParts) >= 3) { $ipParts[count($ipParts)-1] = '****'; $ipParts[count($ipParts)-2] = '****'; }
        $ipMasked = implode(':', $ipParts);
    } else {
        $ipParts = explode('.', $ip); 
        if(count($ipParts) == 4) { $ipParts[3] = '***'; }
        $ipMasked = implode('.', $ipParts);
    }

    $stmt = $pdo->prepare("INSERT INTO suspicious_activity (domain_id, ip_masked, reason) VALUES (0, ?, 'Bloqueio WAF L7: Missing X-API-KEY Header')");
    $stmt->execute([$ipMasked]);
    
    if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Header X-API-KEY ou X-Front18-TOKEN ausente. Barreira SaaS ativa.']));
}

$originClean = '';
if ($origin) {
    if (preg_match('#https?://([^/]+)#', $origin, $matches)) {
        $originClean = $matches[1];
    } else {
        $originClean = preg_replace('#^https?://#', '', $origin);
        $originClean = explode('/', $originClean)[0];
    }
}

// Resolve Identidade SaaS
if ($originClean && $apiKey) {
    $stmt = $pdo->prepare("
        SELECT o.id, o.domain, o.user_id, o.is_active, o.protection_level, o.anti_scraping, o.seo_safe, o.deny_url, o.quota_exceeded_at, o.server_validation_active, o.age_estimation_active, o.display_mode,
               o.terms_url, o.privacy_url, o.color_bg, o.color_text, o.color_primary, o.privacy_config, o.modal_config,
               p.max_requests_per_month, u.is_trial
        FROM saas_origins o 
        JOIN saas_users u ON o.user_id = u.id 
        LEFT JOIN plans p ON u.plan_id = p.id 
        WHERE (REPLACE(REPLACE(o.domain, 'https://', ''), 'http://', '') = ? OR o.api_key = ?)
        LIMIT 1
    ");
    $stmt->execute([$originClean, $apiKey]);
} else {
    // S2S Fallback (Server-to-Server requests sem cabeçalho Origin ou via apiKey)
    $stmt = $pdo->prepare("
        SELECT o.id, o.domain, o.user_id, o.is_active, o.protection_level, o.anti_scraping, o.seo_safe, o.deny_url, o.quota_exceeded_at, o.server_validation_active, o.age_estimation_active, o.display_mode,
               o.terms_url, o.privacy_url, o.color_bg, o.color_text, o.color_primary, o.privacy_config, o.modal_config,
               p.max_requests_per_month, u.is_trial
        FROM saas_origins o 
        JOIN saas_users u ON o.user_id = u.id 
        LEFT JOIN plans p ON u.plan_id = p.id 
        WHERE o.api_key = ?
        LIMIT 1
    ");
    $stmt->execute([$apiKey]);
}
$clienteSaaS = $stmt->fetch();

if (!$clienteSaaS) {
    if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => "API Key Inválida na Hub do Cliente B2B SaaS."]));
}

// VALIDAÇÃO ESTRITA DE ROUBO DE CHAVE (Evitar reciclagem de API Key em múltiplos domínios)
if ($originClean && $clienteSaaS) {
    $originCleanNoWww = str_replace('www.', '', $originClean);
    $domainNoWww = str_replace(['https://', 'http://', 'www.'], '', $clienteSaaS['domain']);
    
    // Se a requisição veio de dentro do nosso iframe hospedado no front18.com, 
    // validamos o site pai (host_site) injetado no payload do app.js
    if ($originCleanNoWww === 'front18.com' || $originCleanNoWww === 'b20robots.com.br') {
        $inputDataTest = json_decode(file_get_contents('php://input'), true);
        if (!empty($inputDataTest['host_site']) && $inputDataTest['host_site'] !== 'Acesso Direto') {
            $originCleanNoWww = str_replace(['https://', 'http://', 'www.'], '', strtolower(trim($inputDataTest['host_site'])));
        }
    }

    // Libera testes locais, mas no servidor de produção exige pareamento idêntico!
    if ($originCleanNoWww !== 'localhost' && $originCleanNoWww !== '127.0.0.1') {
        if ($originCleanNoWww !== $domainNoWww) {
            if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => "B2B WAF: Token de API não pertence ao domínio originador ($originCleanNoWww). Acesso bloqueado para evitar evasão de licença."]));
        }
    }
}

if ($clienteSaaS['is_active'] == 0) {
    if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'CORS Interceptado: Este Domínio está SUSPENSO por falta de pagamento ou Ordem Judicial via Painel Admin.']));
}

$domainId = (int)$clienteSaaS['id'];
$clientId = (int)$clienteSaaS['user_id'];
$isTrial = !empty($clienteSaaS['is_trial']);

// B. LIMITES FINANCEIROS / ENFORCEMENT DE CONTRATO B2B SAE 
if ($action !== 'config') {
    if ($isTrial) {
        $maxRequests = 200;
        $stmtHits = $pdo->prepare("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = ?)");
        $stmtHits->execute([$clientId]);
        $currentHits = (int) $stmtHits->fetchColumn();

        if ($currentHits >= $maxRequests) {
            if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
            http_response_code(402); // 402 Payment Required
            die(json_encode(['success' => false, 'error' => 'Payment Required: Franquia gratuita Esgotada. O SDK suspendeu as validações por limite de Trial. Assine o plano na Hub.']));
        }
    } else {
        $maxRequests = (int)($clienteSaaS['max_requests_per_month'] ?? 150000);
        $currentMonth = date('Y-m');
        $stmtHits = $pdo->prepare("SELECT COUNT(*) FROM access_logs WHERE client_id IN (SELECT id FROM saas_origins WHERE user_id = ?) AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmtHits->execute([$clientId, $currentMonth]);
        $currentHits = (int) $stmtHits->fetchColumn();

        if ($currentHits >= $maxRequests) {
            if (empty($clienteSaaS['quota_exceeded_at'])) {
                $pdo->prepare("UPDATE saas_origins SET quota_exceeded_at = NOW() WHERE id = ?")->execute([$domainId]);
            } else {
                $exceededTime = strtotime($clienteSaaS['quota_exceeded_at']);
                $timeSinceExceeded = time() - $exceededTime;
                
                if ($timeSinceExceeded > 24 * 3600) { 
                    if ($origin) { header("Access-Control-Allow-Origin: $origin"); }
                    http_response_code(429); // 429 Fatal Limit
                    die(json_encode(['success' => false, 'error' => 'Fatal Lock: Carência de 24h Esgotada. Limite mensal excedido. O Bloqueio WAF está ativo.']));
                }
            }
        } else {
            if (!empty($clienteSaaS['quota_exceeded_at'])) {
                $pdo->prepare("UPDATE saas_origins SET quota_exceeded_at = NULL WHERE id = ?")->execute([$domainId]);
            }
        }
    }
}

if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header("Access-Control-Allow-Origin: *");
}

// Configurações de Cache Level 0
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ========================================================
// 1.5. ROTA /CONFIG (Sincronização em Tempo Real do Painel)
// ========================================================
if ($action === 'config') {
    // Trazemos as reconfigurações que o cliente fez no botão principal dele agorinha
    echo json_encode([
        'success' => true,
        'config' => [
            'level'         => (int)$clienteSaaS['protection_level'],
            'anti_scraping' => (int)$clienteSaaS['anti_scraping'],
            'seo_safe'      => (int)$clienteSaaS['seo_safe'],
            'deny_url'      => $clienteSaaS['deny_url'] ?: null,
            'terms_url'     => $clienteSaaS['terms_url'] ?: null,
            'privacy_url'   => $clienteSaaS['privacy_url'] ?: null,
            'color_bg'      => $clienteSaaS['color_bg'] ?: '#0f172a',
            'color_text'    => $clienteSaaS['color_text'] ?: '#f8fafc',
            'color_primary' => $clienteSaaS['color_primary'] ?: '#6366f1',
            'server_validation' => isset($clienteSaaS['server_validation_active']) ? (int)$clienteSaaS['server_validation_active'] : 1,
            'ai_estimation' => isset($clienteSaaS['age_estimation_active']) ? (int)$clienteSaaS['age_estimation_active'] : 0,
            'display_mode'  => $clienteSaaS['display_mode'] ?? 'global_lock',
            'privacy_config' => !empty($clienteSaaS['privacy_config']) ? json_decode($clienteSaaS['privacy_config'], true) : null,
            'modal_config' => !empty($clienteSaaS['modal_config']) ? json_decode($clienteSaaS['modal_config'], true) : null
        ]
    ]);
    exit;
}

// ========================================================
// 2. ROTA /VERIFY (A Carga Principal: Gerando Evidência Analítica)
// ========================================================
if ($action === 'verify') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    // Captura da Biometria Facial do SDK (LGPD-Compliant, apenas numérico)
    $aiAge = null;
    $aiConfidence = null;
    
    if (isset($inputData['ai_age']) && isset($inputData['ai_confidence'])) {
        $aiAge = (int)$inputData['ai_age'];
        $aiConfidence = (float)$inputData['ai_confidence'];
        
        // Bloqueio Hardcoded caso a Rede Neural passe por baixo do limiar
        if ($aiConfidence < 80.0 || $aiAge < 18) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Acesso Negado: A Validação Facial estimou idade inferior ao permitido.']));
        }
    }

    // Gravar a Prova Material Forense e habilitar a Sessão Jurídica (Backend Server State)
    $sessaoBlockchain = (string) SessionManager::verifyUser($domainId, $aiAge, $aiConfidence);

    echo json_encode([
        'success' => true, 
        'message' => 'Contrato Jurídico Aceito e Arquivado. Server Autoridade Criada.',
        'block_hash' => $sessaoBlockchain 
    ]);
    exit;
}

// ========================================================
// 2.1. ROTA /REJECT (Métrica de Queda do Funil)
// ========================================================
if ($action === 'reject') {
    SessionManager::logAudit('REJECTED_CONSENT', $origin, $domainId);
    echo json_encode(['success' => true, 'message' => 'Lead Negativo. Log de Rejeição Ativo Registrado.']);
    exit;
}

// ========================================================
// 3. ROTA /CONTENT (O Desencriptador XOR Master Class)
// ========================================================
if ($action === 'content') {
    
    // JWT/Stateless Token Auth (Sobrevive a qualquer bloqueador de Cookie 3rd-Party da Apple/Chrome)
    $token = $_SERVER['HTTP_X_Front18_TOKEN'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Acesso Negado: Token de Autoridade Ausente. Você não é um Node Reconhecido.']));
    }

    $stmtToken = $pdo->prepare("SELECT id FROM access_logs WHERE current_hash = ? AND (action = 'ALLOWED_AGE_VERIFY' OR action = 'VERIFY_OPT_IN') AND created_at > (NOW() - INTERVAL 24 HOUR) LIMIT 1");
    $stmtToken->execute([$token]);
    
    if (!$stmtToken->fetchColumn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Acesso Negado: Token Blockchain Divergente, Adulterado ou Expirado.']));
    }

    $contentId = $_GET['id'] ?? 'default';

    // Para fins pedagógicos/testes, vamos gerar os HTMLs baseados nos IDs na mosca 
    // Em Produção, isso desceria do Banco de dados do Front18 (Tabela protected_content)
    if ($contentId === 'pacote_vip_399' || $contentId === 'pacote_vip_400') {
        $rawHtml = '
            <i class="ph-bold ph-check-circle text-5xl text-emerald-500 mb-4 block"></i>
            <h3 class="text-2xl font-bold text-white mb-2">Payload Liberado pela API!</h3>
            <p class="text-emerald-400 font-mono text-sm max-w-sm mx-auto mb-4 bg-emerald-500/10 p-3 rounded">
                O seu PHP no (Front18.com) confirmou a Sessão Jurídica. Ele descriptografou tudo e enviou este texto pro Dominio Cliente!
            </p>
            <div class="grid grid-cols-2 gap-4">
                <img src="https://images.unsplash.com/photo-1518779578993-ec3579fee39f?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" style="border-radius:10px; width:100%; height:120px; object-fit:cover;">
                <img src="https://images.unsplash.com/photo-1534447677768-be436bb09401?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" style="border-radius:10px; width:100%; height:120px; object-fit:cover;">
            </div>
            <span class="block text-[9px] mt-4 uppercase tracking-widest text-[#e11d48]">Ameaça contornada. Segurança Nível 2 Ativa.</span>
        ';
    } else {
        $rawHtml = '<div style="color:red">Conteúdo não localizado na Content Delivery Network</div>';
    }

    // Ofusca com XOR simples reverso para o Javascript do SDK não revelar aos "Inspecionar Elemento" fáceis
    $obfuscatedPayload = Crypto::obfuscateResponse($rawHtml);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'secure_payload' => $obfuscatedPayload
    ]);
    exit;
}

// ========================================================
// 4. ROTA /DPO_REPORT (Contato Web com DPO)
// ========================================================
if ($action === 'dpo_report') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    $name = substr(trim($inputData['name'] ?? 'Anônimo'), 0, 255);
    $email = substr(trim($inputData['email'] ?? ''), 0, 255);
    $phone = substr(trim($inputData['phone'] ?? ''), 0, 50);
    $reporterRole = substr(trim($inputData['reporterRole'] ?? ''), 0, 100);
    $violationType = substr(trim($inputData['violationType'] ?? ''), 0, 100);
    $contentUrl = substr(trim($inputData['contentUrl'] ?? ''), 0, 500);
    $message = trim($inputData['message'] ?? '');
    
    if (empty($message)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'A mensagem é obrigatória.']));
    }
    
    $stmt = $pdo->prepare("INSERT INTO saas_dpo_reports (domain_id, reporter_name, reporter_email, reporter_phone, reporter_role, violation_type, content_url, report_message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$domainId, $name, $email, $phone, $reporterRole, $violationType, $contentUrl, $message]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Sua solicitação aos cuidados do DPO foi registrada em nossos sistemas e será avaliada em breve.'
    ]);
    exit;
}

// Se não achar Action
http_response_code(400);
echo json_encode(['error' => 'Ação não suportada no Endpoint B2B.']);
exit;

