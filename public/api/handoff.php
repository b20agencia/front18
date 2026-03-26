<?php
/**
 * Endpoint de Handoff B2B (Cross-Device)
 * Cria a sessão de transição entre o Desktop do usuário e o Smartphone para Validação KYC.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Cross-origin liberado para a geração do QR Code
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../src/Config/config.php';
require_once __DIR__ . '/../../src/Core/Database.php';

try {
    $pdo = Database::getConnection();
    
    // Auto-Migrate silently to ensure table exists
    Database::setup();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Desktop solicitando QR Code
        $domain = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? 'unknown_domain';
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $token = bin2hex(random_bytes(16)); // Token Criptográfico Temporário de 32 caracteres
        $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutos de validade limite
        
        $stmt = $pdo->prepare("INSERT INTO saas_handoff_sessions (token, origin_domain, ip_address, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$token, $domain, $ip, $expires]);

        echo json_encode(['success' => true, 'token' => $token, 'expires_in' => 600]);
        exit;
    }

    if ($action === 'status') {
        // Desktop perguntando se o celular já resolveu o KYC
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['token'] ?? '');
        if (!$token) {
            echo json_encode(['success' => false, 'error' => 'invalid_token']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT status, methods_used FROM saas_handoff_sessions WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            echo json_encode(['success' => false, 'error' => 'expired_or_invalid']);
            exit;
        }

        $methods = $session['methods_used'] ? json_decode($session['methods_used'], true) : [];
        echo json_encode(['success' => true, 'status' => $session['status'], 'methods' => $methods]);
        exit;
    }

    if ($action === 'complete') {
        // O Smartphone (Rota Mobile) enviando o resultado POSITIVO ou NEGATIVO do KYC
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['token'] ?? '');
        $methods = $_POST['methods'] ?? '["liveness"]';
        $status = $_POST['status'] ?? 'approved'; // pending, approved, rejected

        $stmt = $pdo->prepare("UPDATE saas_handoff_sessions SET status = ?, methods_used = ? WHERE token = ? AND status = 'pending' AND expires_at > NOW()");
        $stmt->execute([$status, $methods, $token]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'token_expired_or_already_completed']);
        }
        exit;
    }

    echo json_encode(['error' => 'invalid_action_parameter']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'msg' => $e->getMessage()]);
}
