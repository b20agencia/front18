<?php
/**
 * Arquivo: DashboardActions.php | Controller Isolado para as transações de POST/AJAX do Painel SaaS e DPO
 * Segurança e Isolamento de Tratamento de Dados - Padrão MVC.
 */

class DashboardActions {

    public static function handle($pdo, $userId) {
        $action = $_POST['action'] ?? $_GET['action'] ?? null;
        if (!$action) return;

        // Dependências de Planos (Para validar Restrições Comerciais contra POST indevido)
        $stmtUser = $pdo->prepare("SELECT plan_id FROM saas_users WHERE id = ? LIMIT 1");
        $stmtUser->execute([$userId]);
        $planId = $stmtUser->fetchColumn() ?: 1;

        $stmtPlan = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
        $stmtPlan->execute([$planId]);
        $planDetails = $stmtPlan->fetch(PDO::FETCH_ASSOC);

        $allowedLevel = (int)($planDetails['allowed_level'] ?? 1);
        $hasSeoSafe = (bool)($planDetails['has_seo_safe'] ?? 0);
        $hasAntiScraping = (bool)($planDetails['has_anti_scraping'] ?? 0);

        // 1. Salvar Custom Deny URL
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_fallback') {
            $denyUrlInput = $_POST['deny_url'] ?? '';
            if (empty($denyUrlInput)) {
                $stmtUpdate = $pdo->prepare("UPDATE saas_origins SET deny_url = NULL WHERE user_id = ?");
                $stmtUpdate->execute([$userId]);
            } else {
                if (strpos($denyUrlInput, 'http://') === 0) { $denyUrlInput = str_replace('http://', 'https://', $denyUrlInput); }
                elseif (strpos($denyUrlInput, 'https://') !== 0) { $denyUrlInput = 'https://' . $denyUrlInput; }
                $stmtUpdate = $pdo->prepare("UPDATE saas_origins SET deny_url = ? WHERE user_id = ?");
                $stmtUpdate->execute([$denyUrlInput, $userId]);
            }
            header("Location: ?route=dashboard");
            exit;
        }

        // 2. Adicionar Novo Domínio (Gerar API Key Mestra no Painel)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_domain') {
            $domain_url = strtolower(trim($_POST['domain_url'] ?? ''));
            $domain_url = str_replace(['https://', 'http://', 'www.'], '', $domain_url);
            $domain_url = rtrim(explode('/', $domain_url)[0], '/');
            
            if (!empty($domain_url)) {
                $stmtMax = $pdo->prepare("SELECT max_domains FROM plans p JOIN saas_users u ON p.id = u.plan_id WHERE u.id = ?");
                $stmtMax->execute([$userId]);
                $maxDomains = (int)($stmtMax->fetchColumn() ?: 1);
                
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM saas_origins WHERE user_id = ?");
                $stmtCount->execute([$userId]);
                
                $originCount = (int)$stmtCount->fetchColumn();
                if ($originCount >= $maxDomains) {
                    $_SESSION['dashboard_error'] = "<i class=\"ph-bold ph-warning text-yellow-500 mr-2\"></i> O limite do seu Plano atual é de $maxDomains domínio(s).";
                } else {
                    $newKey = 'SaaS_' . strtoupper(substr(md5(uniqid()), 0, 16)) . rand(10,99);
                    try { 
                        $stmt = $pdo->prepare("INSERT INTO saas_origins (user_id, domain, api_key, protection_level, anti_scraping, seo_safe, is_active) VALUES (?, ?, ?, 1, 0, 0, 1)");
                        $stmt->execute([$userId, $domain_url, $newKey]);
                        $_SESSION['dashboard_success'] = "Domínio '$domain_url' adicionado com sucesso!";
                    } catch(\PDOException $e) {
                        if (strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
                            $_SESSION['dashboard_error'] = "<i class=\"ph-bold ph-x-circle text-red-500 mr-2\"></i> O domínio '$domain_url' já foi registrado.";
                        } else {
                            $_SESSION['dashboard_error'] = "Erro de integridade de banco de dados temporário.";
                        }
                    }
                }
            }
            header("Location: ?route=dashboard#domains");
            exit;
        }

        // 3. Remover Domínio (Libera Franquia de Limite de Planos)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_domain') {
            $domainIdToDelete = (int)$_POST['domain_id'];
            $stmtCheck = $pdo->prepare("SELECT id, domain FROM saas_origins WHERE id = ? AND user_id = ?");
            $stmtCheck->execute([$domainIdToDelete, $userId]);
            $ownDomain = $stmtCheck->fetch();

            if ($ownDomain) {
                $pdo->prepare("DELETE FROM access_logs WHERE client_id = ?")->execute([$domainIdToDelete]);
                $pdo->prepare("DELETE FROM saas_origins WHERE id = ?")->execute([$domainIdToDelete]);
                $_SESSION['dashboard_success'] = "Domínio '{$ownDomain['domain']}' e arquivos vinculados Destruídos.";
            } else {
                $_SESSION['dashboard_error'] = "Ação não permitida.";
            }
            header("Location: ?route=dashboard#domains");
            exit;
        }

        // 4. Salvar Configurações Globais de WAF (Ajax-friendly)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_settings') {
            $level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
            $anti_scrap = isset($_POST['anti_scraping']) ? 1 : 0;
            $seo_safe = isset($_POST['seo_safe']) ? 1 : 0;
            $server_validation = isset($_POST['server_validation_active']) ? 1 : 0;
            $ai_estimation = isset($_POST['age_estimation_active']) ? 1 : 0;
            $display_mode = isset($_POST['display_mode']) && in_array($_POST['display_mode'], ['blur_media', 'global_lock']) ? $_POST['display_mode'] : 'global_lock';
            
            $blur_amount = isset($_POST['blur_amount']) ? (int)$_POST['blur_amount'] : 25;
            $blur_selector = isset($_POST['blur_selector']) ? trim($_POST['blur_selector']) : '';
            if (empty($blur_selector)) {
                $blur_selector = 'img, video, iframe, [data-front18="locked"]';
            }
            
            // Validação de Integridade/Plano (Enforcement Backend)
            if ($level > $allowedLevel) { $level = $allowedLevel; }
            if (!$hasSeoSafe) { $seo_safe = 0; }
            if (!$hasAntiScraping) { $anti_scrap = 0; }
            
            $stmtUpdate = $pdo->prepare("UPDATE saas_origins SET protection_level = ?, anti_scraping = ?, seo_safe = ?, server_validation_active = ?, age_estimation_active = ?, display_mode = ?, blur_amount = ?, blur_selector = ? WHERE user_id = ?");
            $stmtUpdate->execute([$level, $anti_scrap, $seo_safe, $server_validation, $ai_estimation, $display_mode, $blur_amount, $blur_selector, $userId]);
            
            $sync = self::dispatchWordPressSync($pdo, $userId);
            die(json_encode(['success' => true, 'sync' => $sync]));
        }

        // 5. Salvar Personalização UI e URLs Dinâmicas
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_appearance') {
            $c_bg = preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['color_bg'] ?? '') ? $_POST['color_bg'] : '#0f172a';
            $c_txt = preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['color_text'] ?? '') ? $_POST['color_text'] : '#f8fafc';
            $c_pri = preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['color_primary'] ?? '') ? $_POST['color_primary'] : '#6366f1';
            
            $terms = filter_var($_POST['terms_url'] ?? '', FILTER_SANITIZE_URL) ?: null;
            $priv = filter_var($_POST['privacy_url'] ?? '', FILTER_SANITIZE_URL) ?: null;
            $deny = filter_var($_POST['deny_url'] ?? '', FILTER_SANITIZE_URL) ?: null;
            
            $modalConfig = [
                'title' => htmlspecialchars($_POST['modal_title'] ?? 'Conteúdo Protegido'),
                'desc' => htmlspecialchars($_POST['modal_desc'] ?? 'Este portal contém material comercial destinado exclusivamente para o público adulto. É necessário comprovar a sua tutela legal.'),
                'btn_yes' => htmlspecialchars($_POST['modal_btn_yes'] ?? 'Reconhecer e Continuar'),
                'btn_no' => htmlspecialchars($_POST['modal_btn_no'] ?? 'Sou menor de idade (Sair)'),
                'cam_shape' => htmlspecialchars($_POST['cam_shape'] ?? 'circle'),
                'cam_border_color' => preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['cam_border_color'] ?? '') ? $_POST['cam_border_color'] : '#6366f1',
                'cam_glow' => isset($_POST['cam_glow']) ? true : false,
                'modal_border_color' => preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['modal_border_color'] ?? '') ? $_POST['modal_border_color'] : ''
            ];
            $modalJson = json_encode($modalConfig);
            
            $stmtUpd = $pdo->prepare("UPDATE saas_origins SET color_bg = ?, color_text = ?, color_primary = ?, terms_url = ?, privacy_url = ?, deny_url = ?, modal_config = ? WHERE user_id = ?");
            $stmtUpd->execute([$c_bg, $c_txt, $c_pri, $terms, $priv, $deny, $modalJson, $userId]);
            
            $sync = self::dispatchWordPressSync($pdo, $userId);
            die(json_encode(['success' => true]));
        }

        // 6. Cofre DPO: Descriptografar / Validação
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'dpo_unlock') {
            $insertedKey = trim($_POST['dpo_key'] ?? '');
            
            $stmtDomain = $pdo->prepare("SELECT privacy_config FROM saas_origins WHERE user_id = ?");
            $stmtDomain->execute([$userId]);
            $conf = $stmtDomain->fetchColumn();
            $privConf = !empty($conf) ? json_decode($conf, true) : [];
            
            $hashMestre = $privConf['dpo_master_key'] ?? ''; 
            
            // FASE 2: Transição Segura. Verificamos se está vazio, se é texto puro legado ou Argon2
            $isValid = false;
            if (empty($hashMestre)) {
                if ($insertedKey === 'front18-master') $isValid = true; // Fallback hardcoded
            } else {
                if (password_verify($insertedKey, $hashMestre)) {
                    $isValid = true;
                } elseif ($insertedKey === $hashMestre) {
                    $isValid = true; // Senhas salvas em plain text antes dessa atualização
                }
            }

            if ($isValid) {
                $_SESSION['dpo_unlocked_' . $userId] = true;
                die(json_encode(['success' => true]));
            }
            die(json_encode(['success' => false, 'error' => 'Chave Criptográfica Inválida. Acesso revogado.']));
        }

        // 7. Cofre DPO: Trancar
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'dpo_lock') {
            unset($_SESSION['dpo_unlocked_' . $userId]);
            die(json_encode(['success' => true]));
        }

        // 8. Cofre DPO: Encerrar Tickets
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'dpo_resolve') {
            if (!isset($_SESSION['dpo_unlocked_' . $userId])) {
                die(json_encode(['success' => false, 'error' => 'Sessão DPO bloqueada. É necessário Passkey.']));
            }
            $reportId = (int)($_POST['report_id'] ?? 0);
            $notes = trim($_POST['resolution_notes'] ?? '');
            
            $stmtDomain = $pdo->prepare("SELECT id FROM saas_origins WHERE user_id = ?");
            $stmtDomain->execute([$userId]);
            $domId = $stmtDomain->fetchColumn();
            
            if ($domId && $reportId && $notes) {
                $stmtUpdate = $pdo->prepare("UPDATE saas_dpo_reports SET status = 'resolved', report_notes = ?, resolved_at = NOW() WHERE id = ? AND domain_id = ?");
                $stmtUpdate->execute([$notes, $reportId, $domId]);
                die(json_encode(['success' => true]));
            }
            die(json_encode(['success' => false, 'error' => 'Parâmetros inválidos para resolução do DPO.']));
        }

        // 9. Salvar Configurações LGPD e Gerar Hash da Master Key
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_privacy') {
            
            $plainMasterKey = trim($_POST['dpo_master_key'] ?? '');
            if (empty($plainMasterKey)) $plainMasterKey = 'front18-master';

            // Criptografia Argon2/Bcrypt do servidor DPO
            $hashedMasterKey = password_hash($plainMasterKey, PASSWORD_DEFAULT);

            $privacyConfig = [
                'dpo_email' => filter_var($_POST['dpo_email'] ?? '', FILTER_SANITIZE_EMAIL),
                'dpo_title' => htmlspecialchars($_POST['dpo_title'] ?? 'Oficial de Dados (DPO)'),
                'banner_title' => htmlspecialchars($_POST['banner_title'] ?? 'Aviso de Privacidade e LGPD'),
                'banner_text' => htmlspecialchars($_POST['banner_text'] ?? 'Utilizamos cookies essenciais e avaliativos para garantir o funcionamento seguro deste portal.'),
                'btn_accept' => htmlspecialchars($_POST['btn_accept'] ?? 'Aceitar Essenciais'),
                'btn_reject' => htmlspecialchars($_POST['btn_reject'] ?? 'Rejeitar Opcionais'),
                'age_rating' => htmlspecialchars($_POST['age_rating'] ?? '18+'),
                'allow_reject' => isset($_POST['allow_reject']) ? true : false,
                'has_analytics' => isset($_POST['has_analytics']) ? true : false,
                'has_marketing' => isset($_POST['has_marketing']) ? true : false,
                'has_meta' => isset($_POST['has_meta']) ? true : false,
                'has_tiktok' => isset($_POST['has_tiktok']) ? true : false,
                'has_heatmaps' => isset($_POST['has_heatmaps']) ? true : false,
                'dpo_master_key' => $hashedMasterKey // Salvo no banco já criptografado!
            ];
            
            $jsonConfig = json_encode($privacyConfig);
            
            $stmtUpd = $pdo->prepare("UPDATE saas_origins SET privacy_config = ? WHERE user_id = ?");
            $stmtUpd->execute([$jsonConfig, $userId]);
            
            $sync = self::dispatchWordPressSync($pdo, $userId);
            die(json_encode(['success' => true]));
        }

        // 10. Salvar URL do WordPress e Automação Webhook
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_wp') {
            $wp_url = filter_var($_POST['wp_url'] ?? '', FILTER_SANITIZE_URL);
            if ($wp_url) { $wp_url = rtrim($wp_url, '/'); }
            
            $wp_rules = [
                'global' => isset($_POST['wp_global']) ? true : false,
                'home'   => isset($_POST['wp_home']) ? true : false,
                'cpts'   => isset($_POST['wp_cpts']) && is_array($_POST['wp_cpts']) ? array_map('htmlspecialchars', $_POST['wp_cpts']) : []
            ];
            
            $jsonRules = json_encode($wp_rules);
            
            $stmtUpd = $pdo->prepare("UPDATE saas_origins SET wp_url = ?, wp_rules = ? WHERE user_id = ?");
            $stmtUpd->execute([$wp_url, $jsonRules, $userId]);
            
            $sync = self::dispatchWordPressSync($pdo, $userId);
            die(json_encode(['success' => true, 'push_status' => $sync['status'], 'push_msg' => $sync['msg']]));
        }

        // 11. Endpoint Proxied da Biblioteca de Arquivos Grã Mestre (Frontend <=> WP Cliente)
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load_wp_media') {
            $page = (int)($_GET['page'] ?? 1);
            
            $stmtOrigin = $pdo->prepare("SELECT wp_url, api_key FROM saas_origins WHERE user_id = ? LIMIT 1");
            $stmtOrigin->execute([$userId]);
            $origin = $stmtOrigin->fetch(PDO::FETCH_ASSOC);
            
            $wp_url = rtrim($origin['wp_url'] ?? '', '/');
            if (!$wp_url || !$origin['api_key']) {
                die(json_encode(['success' => false, 'error' => 'Endpoint WordPress ou Chave de API não localizada.']));
            }
            
            $search = urlencode($_GET['search'] ?? '');
            $folder = urlencode($_GET['folder'] ?? '');
            
            $ch = curl_init("$wp_url/wp-json/front18/v1/media?page=$page&per_page=48&search=$search&folder=$folder");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $origin['api_key']]);
            $res = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            header('Content-Type: application/json');
            if ($httpcode == 200 && $res) {
                die($res); 
            } else {
                die(json_encode(['success' => false, 'error' => "Falha PUSH. Código HTTP $httpcode. Verifique o Plugin B2B."]));
            }
        }

        // 12. Salvação dos Blur Protections Específicos por Arquivo (IDs array)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_wp_media') {
            $protected_ids = empty($_POST['protected_ids']) ? [] : $_POST['protected_ids'];
            if (!is_array($protected_ids)) { $protected_ids = []; }
            
            $clean_ids = array_values(array_unique(array_filter(array_map('intval', $protected_ids))));
            $json_ids = json_encode($clean_ids);
            
            try {
                $pdo->exec("ALTER TABLE saas_origins ADD COLUMN protected_media_ids LONGTEXT NULL AFTER blur_selector");
            } catch (PDOException $e) { }
            
            try {
                $stmtUpd = $pdo->prepare("UPDATE saas_origins SET protected_media_ids = ? WHERE user_id = ?");
                $stmtUpd->execute([$json_ids, $userId]);
                
                $sync = self::dispatchWordPressSync($pdo, $userId);
                
                header('Content-Type: application/json');
                die(json_encode(['success' => true, 'total' => count($clean_ids), 'push_status' => $sync['status']]));
            } catch (Exception $e) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'error' => 'Falha interna: ' . $e->getMessage()]));
            }
        }
    }

    // ======== WEHOOK SYNC HELPER (MÉTODO PRIVADO DO CONTROLLER) ========
    private static function dispatchWordPressSync($pdo, $userId) {
        try {
            $pdo->exec("ALTER TABLE saas_origins ADD COLUMN protected_media_ids LONGTEXT NULL AFTER blur_selector");
        } catch(PDOException $e) { }

        $stmtOrigin = $pdo->prepare("SELECT api_key, display_mode, protection_level, color_bg, color_primary, color_text, blur_amount, blur_selector, protected_media_ids, wp_rules, wp_url FROM saas_origins WHERE user_id = ? LIMIT 1");
        try {
            $stmtOrigin->execute([$userId]);
        } catch(Exception $e) {
            return ['status' => false, 'msg' => 'Falha DB na origem: ' . $e->getMessage()];
        }
        
        $origin = $stmtOrigin->fetch(PDO::FETCH_ASSOC);
        if (empty($origin['wp_url']) || empty($origin['api_key'])) return ['status' => false, 'msg' => 'Sem URL do WP configurada.'];
        
        $wp_url = rtrim($origin['wp_url'], '/');
        $wp_rules = !empty($origin['wp_rules']) ? json_decode($origin['wp_rules'], true) : ['global'=>false,'home'=>false,'cpts'=>[]];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $wp_url . '/wp-json/front18/v1/sync');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $protectedMedia = !empty($origin['protected_media_ids']) ? json_decode($origin['protected_media_ids'], true) : [];

        $pushData = [
            'rules'  => $wp_rules,
            'protected_ids' => $protectedMedia,
            'api_key' => $origin['api_key'],
            'endpoint' => 'https://' . $_SERVER['HTTP_HOST'] . '/public/api/track.php',
            'script_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/public/sdk/front18.js?v=' . time(),
            'config' => [
                'level'         => isset($origin['protection_level']) ? (int)$origin['protection_level'] : 1,
                'display_mode'  => !empty($origin['display_mode']) ? $origin['display_mode'] : 'global_lock',
                'color_bg'      => !empty($origin['color_bg']) ? $origin['color_bg'] : '#0f172a',
                'color_primary' => !empty($origin['color_primary']) ? $origin['color_primary'] : '#6366f1',
                'color_text'    => !empty($origin['color_text']) ? $origin['color_text'] : '#f8fafc',
                'blur_amount'   => isset($origin['blur_amount']) ? (int)$origin['blur_amount'] : 25,
                'blur_selector' => !empty($origin['blur_selector']) ? $origin['blur_selector'] : 'img, video, iframe, [data-front18="locked"]'
            ]
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pushData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $origin['api_key']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode >= 200 && $httpcode < 300) {
            return ['status' => true, 'msg' => 'Sincronizado na CDN Edge!'];
        }
        return ['status' => false, 'msg' => "WAF Bloqueado: HTTP $httpcode. $response"];
    }
}
