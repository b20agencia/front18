<?php
/**
 * Arquivo: SessionManager.php | Gerenciador Avançado de Sessões (Session Fixation, Hijacking Shield)
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
/**
 * Guardião de Autorização via Cookies PHPSESSID Customizados (Server-Side Trust)
 * Controla Duração, Criação e Quebra de acesso pra Requests Fraudulentas.
 */
class SessionManager {
    
    public static function start() {
        // Renomeia pra evadir heurísticas genéricas de WAF/Bots e dar cara de Produto
        session_name(FRONT18_SESSION_NAME);
        
        // A verdadeira engrenagem SaaS: Third-Party Contexts
        // O Chrome descarta cookies de SDKs embedados se Não for SameSite=None + Secure.
        session_set_cookie_params([
            'lifetime' => FRONT18_SESSION_LIFETIME,
            'path' => '/',
            'samesite' => 'None', // Permite o Cookie do Edge/Chrome/Safari funcionar num WordPress de cliente
            'secure' => true // HTTPS é 100% obrigatório em SameSite=None em ambiente de produção
        ]);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function logSystemIntegrity($clientId, $action, $details) {
        require_once __DIR__ . '/Database.php';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO system_audit_logs (client_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$clientId, $action, $details]);
    }

    /**
     * Extração Rigorosa de IP (Ignora o Load Balancer e busca a Borda Real do Usuário)
     */
    public static function getRealIp() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        // Lista de prioridade dos cabeçalhos repassados por Proxies, AWS ALB, NGINX e Cloudflare
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Padrão-Ouro do Cloudflare
            'HTTP_X_REAL_IP',        // Padrão-Ouro do Nginx
            'HTTP_X_FORWARDED_FOR'   // Padrão de Proxies em Cadeia (AWS ALB)
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For manda uma fila (client, proxy1, proxy2...). Pega só a ponta da Tropa (0).
                $headparts = explode(',', $_SERVER[$header]);
                $candidate = trim($headparts[0]);
                
                // Crivo Anti-Spoofing: Alguém poderia mandar X-Forwarded-For: <script> no cabeçalho
                if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                    $ip = $candidate;
                    break;
                }
            }
        }
        return $ip;
    }

    public static function logAudit($action, $customOrigin = null, $clientId = 0, $aiAge = null, $aiConfidence = null) {
        require_once __DIR__ . '/../Config/config.php';
        require_once __DIR__ . '/Database.php';
        $pdo = Database::getConnection();

        $rawIp = self::getRealIp();
        $logIp = (defined('FRONT18_LGPD_MASK') && FRONT18_LGPD_MASK) ? preg_replace('/\.\d+$/', '.0', $rawIp) : $rawIp;
        $uaSubstr = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $origin = $customOrigin ?: ($_SERVER['HTTP_ORIGIN'] ?? 'direct_browser_hit');

        // Hash Encadeada Elevada (Blockchain Leve) -> Garante Perícia Segura
        $stmtL = $pdo->query("SELECT current_hash FROM access_logs ORDER BY id DESC LIMIT 1");
        $prevHash = $stmtL->fetchColumn() ?: hash('sha256', 'GENESIS_BLOCK_001');

        $timestamp = date('c'); 
        $aiHashData = $aiAge ? ((string)$aiAge . (string)$aiConfidence) : 'no_ai';
        $currentHash = hash('sha256', $logIp . $uaSubstr . $timestamp . $aiHashData . $prevHash);
        $serverSignature = (defined('FRONT18_SECRET_KEY')) ? hash_hmac('sha256', $currentHash, FRONT18_SECRET_KEY) : 'missing_key';

        // Gravando Evidência Analítica do Documento Legal (Terms)
        $termsVersion = defined('FRONT18_TERMS_VERSION') ? FRONT18_TERMS_VERSION : 'v0.0';
        $termsHash = 'not_found';
        if (defined('FRONT18_TERMS_FILE') && file_exists(FRONT18_TERMS_FILE)) {
             $termsHash = hash('sha256', file_get_contents(FRONT18_TERMS_FILE) ?: 'MISSING_FILE');
        }

        // Key Versioning para Auditoria
        $keyVersion = defined('FRONT18_KEY_VERSION') ? FRONT18_KEY_VERSION : 'undefined';

        $stmt = $pdo->prepare("INSERT INTO access_logs (client_id, siteOrigin, ip_address, user_agent, action, session_id, prev_hash, current_hash, server_signature, key_version, terms_version, terms_hash, ai_estimated_age, ai_confidence_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $clientId,
            $origin,
            $logIp,
            $uaSubstr,
            $action,
            session_id() ?: 'no_session',
            $prevHash,
            $currentHash,
            $serverSignature,
            $keyVersion,
            $termsVersion,
            $termsHash,
            $aiAge,
            $aiConfidence
        ]);
        return $currentHash;
    }

    public static function verifyUser($clientId = 0, $aiAge = null, $aiConfidence = null, $cpfMask = null) {
        self::start();
        $_SESSION['client_id'] = $clientId;
        $_SESSION['agegate_authorized'] = true;
        $_SESSION['agegate_authorized_expires'] = time() + FRONT18_SESSION_LIFETIME;
        
        // Fase 1: Ancorar sessão contra sequestro de cookie (Hijack)
        $_SESSION['client_ip'] = self::getRealIp();
        $_SESSION['client_ua'] = $_SERVER['HTTP_USER_AGENT'];

        // Slider Anti-Replay
        $_SESSION['requests'] = [];

        require_once __DIR__ . '/../Config/config.php';
        require_once __DIR__ . '/Database.php';
        require_once __DIR__ . '/Crypto.php';
        $pdo = Database::getConnection();

        // Expurgar Logs Antigos (Retenção LGPD baseada no config.php)
        if (defined('FRONT18_RETENTION_DAYS')) {
            $pdo->exec("DELETE FROM access_logs WHERE created_at < NOW() - INTERVAL " . FRONT18_RETENTION_DAYS . " DAY");
        }

        $rawIp = self::getRealIp();
        $logIp = (defined('FRONT18_LGPD_MASK') && FRONT18_LGPD_MASK) ? preg_replace('/\.\d+$/', '.0', $rawIp) : $rawIp; // LGPD Mask

        // 🛑 Nova Barreira Empresarial: GeoIP
        if (defined('FRONT18_ALLOWED_COUNTRIES') && !empty(FRONT18_ALLOWED_COUNTRIES)) {
            $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null;
            if ($country && !in_array($country, FRONT18_ALLOWED_COUNTRIES)) {
                self::logAudit('BLOCKED_GEO_IP', null, $clientId);
                http_response_code(403);
                die(json_encode(['success' => false, 'error' => 'Acesso Negado: Região geográfica não permitida pelos Termos de Uso corporativos.']));
            }
        }

        // Fase 3: Shield Anti-Spam / Rate Limiting Seguro (Protegendo contra DDoS)
        $stmtLimit = $pdo->prepare("SELECT COUNT(*) FROM access_logs WHERE ip_address = ? AND action = 'VERIFY_OPT_IN' AND created_at > (NOW() - INTERVAL 10 MINUTE)");
        $stmtLimit->execute([$logIp]);
        if ($stmtLimit->fetchColumn() >= 15) {
            self::logAudit('BLOCKED_RATE_LIMIT', null, $clientId);
            http_response_code(429); // 429 Too Many Requests
            die(json_encode(['success' => false, 'error' => 'Bloqueio de Segurança: Excesso de tentativas. Risco de mitigação ativo.']));
        }

        // Fase 1 e 2: Gravar Registro à Prova de Adulteração (Sucesso)
        $actionStr = 'VERIFY_OPT_IN';
        if ($cpfMask) {
            $actionStr .= " (KYC_CPF: {$cpfMask})";
        }
        return self::logAudit($actionStr, null, $clientId, $aiAge, $aiConfidence);
    }

    public static function hasAccess() {
        self::start();
        $clientId = $_SESSION['client_id'] ?? 0;
        if (empty($_SESSION['agegate_authorized']) || empty($_SESSION['agegate_authorized_expires'])) {
            return false;
        }

        // Janela Deslizante de Tempo Estrito (Cortando Bots Scrapers que clonaram o cookie)
        if (!isset($_SESSION['requests'])) {
            $_SESSION['requests'] = [];
        }
        $_SESSION['requests'][] = time();
        $_SESSION['requests'] = array_filter(
            $_SESSION['requests'],
            fn($t) => $t > time() - 60
        );

        if (count($_SESSION['requests']) > 30) {
            self::logAudit('BLOCKED_SESSION_ABUSE', null, $clientId);
            self::destroy();
            return false;
        }

        // Detecta validade nativa morta
        if (time() > $_SESSION['agegate_authorized_expires']) {
            self::logAudit('BLOCKED_EXPIRED', null, $clientId);
            self::destroy();
            return false;
        }

        // Fase 1: Detecção de Mutação de Instância (Anti-Hijack)
        $currentIp = self::getRealIp();
        $currentUa = $_SERVER['HTTP_USER_AGENT'];

        if (isset($_SESSION['client_ip']) && $_SESSION['client_ip'] !== $currentIp) {
            self::logAudit('BLOCKED_HIJACK_IP', null, $clientId);
            self::destroy();
            return false;
        }

        if (isset($_SESSION['client_ua']) && $_SESSION['client_ua'] !== $currentUa) {
            self::logAudit('BLOCKED_HIJACK_UA', null, $clientId);
            self::destroy();
            return false;
        }

        return true;
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
