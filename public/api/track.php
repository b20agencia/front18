<?php
/**
 * Arquivo: track.php | Endpoint de Tracking de Logs Biométricos e Bloqueios
 * @author Documentado por Gil Santos e Leandro Satt (Refatorado pelo Antigravity/Arquitetura)
 * @projeto Front18 Pro SaaS Architecture
 */
require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Core/SessionManager.php';
require_once __DIR__ . '/../../src/Core/Crypto.php';

// FASE 1: Segurança - Zero Vazar Stack Trace em Produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// FASE 1: Cabeçalhos Universais de Segurança API
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_X_FRONT18_TOKEN'] ?? $_GET['key'] ?? ''; 
$action = $_GET['action'] ?? 'content';

Database::setup();
$pdo = Database::getConnection();

$originClean = '';
$originCleanNoWww = '';
if ($origin) {
    if (preg_match('#https?://([^/]+)#', $origin, $matches)) {
        $originClean = $matches[1];
    } else {
        $originClean = preg_replace('#^https?://#', '', $origin);
        $originClean = explode('/', $originClean)[0];
    }
    $originCleanNoWww = str_replace('www.', '', $originClean);
}

if ($originCleanNoWww) {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        $stmtOrigin = $pdo->prepare("SELECT id FROM saas_origins WHERE REPLACE(REPLACE(domain, 'https://', ''), 'http://', '') = ? LIMIT 1");
        $stmtOrigin->execute([$originCleanNoWww]);
        if ($stmtOrigin->fetchColumn()) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY, X-Front18-Token, Cache-Control, Pragma, Accept');
            http_response_code(200);
            exit;
        }
        http_response_code(403);
        exit;
    } else {
        // Enviar CORS para conexões aprovadas durante uso contínuo
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(403);
    exit;
}

if (!$apiKey) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Header X-API-KEY ou X-Front18-TOKEN ausente. Barreira SaaS ativa.']));
}

// B2B Routing identity
if ($originCleanNoWww && $apiKey) {
    // FASE 3: A consulta agora traz os LIMITES O(1) resolvendo o Table Scan de rate_limiting
    $stmt = $pdo->prepare("SELECT o.*, p.max_requests_per_month, u.is_trial FROM saas_origins o JOIN saas_users u ON o.user_id = u.id LEFT JOIN plans p ON u.plan_id = p.id WHERE (REPLACE(REPLACE(o.domain, 'https://', ''), 'http://', '') = ? OR o.api_key = ?) LIMIT 1");
    $stmt->execute([$originCleanNoWww, $apiKey]);
} else {
    $stmt = $pdo->prepare("SELECT o.*, p.max_requests_per_month, u.is_trial FROM saas_origins o JOIN saas_users u ON o.user_id = u.id LEFT JOIN plans p ON u.plan_id = p.id WHERE o.api_key = ? LIMIT 1");
    $stmt->execute([$apiKey]);
}
$clienteSaaS = $stmt->fetch();

if (!$clienteSaaS) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => "API Key Inválida na Hub do Cliente B2B SaaS."]));
}

$domainId = (int)$clienteSaaS['id'];

// Validação Estrita de Roubo de Chave
if ($originCleanNoWww && $clienteSaaS) {
    $domainNoWww = str_replace(['https://', 'http://', 'www.'], '', $clienteSaaS['domain']);
    
    if ($originCleanNoWww === 'front18.com' || $originCleanNoWww === 'b20robots.com.br') {
        $inputDataTest = @json_decode(file_get_contents('php://input'), true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($inputDataTest['host_site']) && $inputDataTest['host_site'] !== 'Acesso Direto') {
            $originCleanNoWww = str_replace(['https://', 'http://', 'www.'], '', strtolower(trim($inputDataTest['host_site'])));
        }
    }

    if ($originCleanNoWww !== 'localhost' && $originCleanNoWww !== '127.0.0.1') {
        if ($originCleanNoWww !== $domainNoWww) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => "B2B WAF: Token de API não pertence ao domínio originador ($originCleanNoWww). Acesso bloqueado."]));
        }
    }
}

if ($clienteSaaS['is_active'] == 0) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'CORS Interceptado: Este Domínio está SUSPENSO.']));
}

// Emissão dos Headers CORS pós-validação de Banco de dados
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header("Access-Control-Allow-Origin: *");
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$domainId = (int)$clienteSaaS['id'];
$clientId = (int)$clienteSaaS['user_id'];
$isTrial = !empty($clienteSaaS['is_trial']);

// FASE 3: Rate Limiting de Alta Performance com DB Otimizado
if ($action !== 'config') {
    $currentHits = (int)($clienteSaaS['current_month_requests'] ?? 0);
    
    if ($isTrial) {
        $maxRequests = 200;
        if ($currentHits >= $maxRequests) {
            http_response_code(402);
            die(json_encode(['success' => false, 'error' => 'Payment Required: Limite de Trial Esgotado.']));
        }
    } else {
        $maxRequests = (int)($clienteSaaS['max_requests_per_month'] ?? 150000);
        if ($currentHits >= $maxRequests) {
            if (empty($clienteSaaS['quota_exceeded_at'])) {
                $pdo->prepare("UPDATE saas_origins SET quota_exceeded_at = NOW() WHERE id = ?")->execute([$domainId]);
            } else {
                $exceededTime = strtotime($clienteSaaS['quota_exceeded_at']);
                if ((time() - $exceededTime) > 24 * 3600) { 
                    http_response_code(429);
                    die(json_encode(['success' => false, 'error' => 'Fatal Lock: Carência Esgotada. Limite mensal excedido.']));
                }
            }
        } else {
            if (!empty($clienteSaaS['quota_exceeded_at'])) {
                $pdo->prepare("UPDATE saas_origins SET quota_exceeded_at = NULL WHERE id = ?")->execute([$domainId]);
            }
        }
    }
    
    // Atualiza o contador de hits apenas localmente no banco (Elimina Table Scan)
    try {
        $pdo->prepare("UPDATE saas_origins SET current_month_requests = current_month_requests + 1 WHERE id = ?")->execute([$domainId]);
    } catch (\PDOException $e) { }
}

// FASE 1: Lendo JSON Body com Validação Rigorosa de Parse (Anti Exception)
$inputData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $inputData = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'O Payload Body contém JSON inválido ou corrompido.']));
        }
    }
}

if ($action === 'config') {
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
            'blur_amount'   => isset($clienteSaaS['blur_amount']) ? (int)$clienteSaaS['blur_amount'] : 25,
            'blur_selector' => !empty($clienteSaaS['blur_selector']) ? $clienteSaaS['blur_selector'] : 'img, video, iframe, [data-front18="locked"]',
            'privacy_config' => !empty($clienteSaaS['privacy_config']) ? json_decode($clienteSaaS['privacy_config'], true) : null,
            'modal_config' => !empty($clienteSaaS['modal_config']) ? json_decode($clienteSaaS['modal_config'], true) : null
        ]
    ]);
    exit;
}

if ($action === 'verify') {
    $aiAge = isset($inputData['ai_age']) ? (int)$inputData['ai_age'] : null;
    $aiConfidence = isset($inputData['ai_confidence']) ? (float)$inputData['ai_confidence'] : null;
    $cpfMask = null;
    
    if (!empty($inputData['cpf_mask'])) {
        $cpfMask = strip_tags($inputData['cpf_mask']); 
        if (!preg_match('/^\*\*\*\.\*\*\*\.\d{3}-\d{2}$/', $cpfMask)) {
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'Acesso Negado: Mascara CPF inválida.']));
        }
    }
    
    if ($aiAge !== null && $aiConfidence !== null) {
        if ($aiConfidence < 80.0 || $aiAge < 18) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Acesso Negado: Validação Facial estimou idade inferior ao permitido.']));
        }
        if ($aiAge < 21 && empty($cpfMask)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Acesso Negado: Necessário CPF para este limiar de ID.']));
        }
    }

    $sessaoBlockchain = (string) SessionManager::verifyUser($domainId, $aiAge, $aiConfidence, $cpfMask);

    echo json_encode(['success' => true, 'message' => 'Contrato Autoridade Criado com Sucesso.', 'block_hash' => $sessaoBlockchain]);
    exit;
}

if ($action === 'reject') {
    SessionManager::logAudit('REJECTED_CONSENT', $origin, $domainId);
    echo json_encode(['success' => true, 'message' => 'Log de Rejeição Ativo Registrado.']);
    exit;
}

if ($action === 'content') {
    $token = $_SERVER['HTTP_X_FRONT18_TOKEN'] ?? '';
    if (empty($token)) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Acesso Negado: Token de Autoridade Ausente.']));
    }

    $stmtToken = $pdo->prepare("SELECT id FROM access_logs WHERE current_hash = ? AND (action = 'ALLOWED_AGE_VERIFY' OR action = 'VERIFY_OPT_IN') AND created_at > (NOW() - INTERVAL 24 HOUR) LIMIT 1");
    $stmtToken->execute([$token]);
    if (!$stmtToken->fetchColumn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Acesso Negado: Token de Sessão Expirado ou Adulterado.']));
    }

    $contentId = $_GET['id'] ?? 'default';
    if ($contentId === 'pacote_vip_399' || $contentId === 'pacote_vip_400') {
        $rawHtml = '
            <i class="ph-bold ph-check-circle text-5xl text-emerald-500 mb-4 block"></i>
            <h3 class="text-2xl font-bold text-white mb-2">Mídias Reais Liberadas 🔓</h3>
            <p class="text-emerald-400 font-mono text-sm max-w-sm mx-auto mb-4 bg-emerald-500/10 p-3 rounded">
                O AES-256 GCM descriptografou o conteúdo sob demanda! Nada vazou pro Código-Fonte HTML nem causou Flicker.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <img src="https://images.unsplash.com/photo-1518779578993-ec3579fee39f?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" style="border-radius:10px; width:100%; height:120px; object-fit:cover;">
                <img src="https://images.unsplash.com/photo-1534447677768-be436bb09401?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" style="border-radius:10px; width:100%; height:120px; object-fit:cover;">
            </div>
            <span class="block text-[9px] mt-4 uppercase tracking-widest text-[#e11d48]">Anti-Flicker Skeleton Injection Concluído com Sucesso.</span>
        ';
    } else {
        $rawHtml = '<div style="color:red">Conteúdo real protegido ou não localizado CDN</div>';
    }

    // FASE 2: Criptografia Oculta Dinâmica Session-Based (AES-256)
    $obfuscatedPayload = Crypto::encryptResponse($rawHtml, $token);

    http_response_code(200);
    echo json_encode(['success' => true, 'secure_payload' => $obfuscatedPayload]);
    exit;
}

if ($action === 'dpo_report') {
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
    
    echo json_encode(['success' => true, 'message' => 'Solicitação registrada no nosso canal DPO Legal.']);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Ação não suportada.']);
exit;
