<?php
/**
 * Padrão Singleton de Conexão PDO
 * Garante que abrimos apenas 1 conexão MySQL durante toda a execução da API.
 */
class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . FRONT18_DB_HOST . ";dbname=" . FRONT18_DB_NAME . ";charset=utf8mb4",
                    FRONT18_DB_USER,
                    FRONT18_DB_PASS
                );
                // Lança exceções automáticas em todos os erros de MySQL (Melhor prática OOP)
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Previne crash com dump de senha em log, retorna Error 500 JSON Friendly
                http_response_code(500);
                die(json_encode(['success' => false, 'error' => 'Servidor de Distribuição temporariamente inacessível.']));
            }
        }
        return self::$instance;
    }

    public static function setup() {
        $pdo = self::getConnection();
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS protected_content (
                id VARCHAR(100) PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                html_content LONGTEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS access_logs (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL DEFAULT 0,
                siteOrigin VARCHAR(150) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT NOT NULL,
                action VARCHAR(50) NOT NULL,
                session_id VARCHAR(128) NOT NULL,
                prev_hash VARCHAR(64) NULL,
                current_hash VARCHAR(64) NULL,
                server_signature VARCHAR(64) NULL,
                key_version VARCHAR(10) NULL,
                terms_version VARCHAR(20) NULL,
                terms_hash VARCHAR(64) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (ip_address),
                INDEX (client_id)
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS saas_origins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                domain VARCHAR(255) UNIQUE NOT NULL,
                api_key VARCHAR(100) UNIQUE NULL,
                terms_url VARCHAR(255) NULL,
                privacy_url VARCHAR(255) NULL,
                privacy_config JSON NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        try {
            $pdo->exec("ALTER TABLE saas_origins ADD COLUMN privacy_config JSON NULL");
        } catch (\PDOException $e) { }
        try {
            $pdo->exec("ALTER TABLE saas_origins ADD COLUMN modal_config JSON NULL");
        } catch (\PDOException $e) { }

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL DEFAULT 0,
                action VARCHAR(100) NOT NULL,
                details TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS saas_dpo_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                domain_id INT NOT NULL,
                reporter_name VARCHAR(255) NULL,
                reporter_email VARCHAR(255) NULL,
                reporter_phone VARCHAR(50) NULL,
                reporter_role VARCHAR(100) NULL,
                violation_type VARCHAR(100) NULL,
                content_url VARCHAR(500) NULL,
                report_message TEXT NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (domain_id)
            )
        ");

        try { $pdo->exec("ALTER TABLE saas_dpo_reports ADD COLUMN violation_type VARCHAR(100) NULL"); } catch (\PDOException $e) {}
        try { $pdo->exec("ALTER TABLE saas_dpo_reports ADD COLUMN content_url VARCHAR(500) NULL"); } catch (\PDOException $e) {}
        try { $pdo->exec("ALTER TABLE saas_dpo_reports ADD COLUMN reporter_role VARCHAR(100) NULL"); } catch (\PDOException $e) {}

        // Setup das Tabelas de Precificação, Configuração de Domínio e Atividades Suspeitas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS plans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                max_domains INT NOT NULL,
                max_requests_per_month BIGINT NOT NULL,
                is_featured TINYINT(1) DEFAULT 0,
                allowed_level INT DEFAULT 1,
                has_seo_safe TINYINT(1) DEFAULT 0,
                has_anti_scraping TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS suspicious_activity (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                domain_id INT NOT NULL,
                ip_masked VARCHAR(50) NOT NULL,
                reason VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (domain_id)
            )
        ");

        // Alimentar Planos Padrão do Sistema (Automático)
        $stmtPlans = $pdo->query("SELECT COUNT(*) FROM plans");
        if ($stmtPlans->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO plans (name, price, max_domains, max_requests_per_month, is_featured, allowed_level, has_seo_safe, has_anti_scraping) VALUES 
                ('SaaS Start Ouro', 149.90, 1, 150000, 1, 2, 1, 0),
                ('Corporativo Edge', 399.90, 3, 500000, 0, 3, 1, 1)
            ");
        }

        // Nova Arquitetura de Usuários do Painel (SaaS)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS saas_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role VARCHAR(50) DEFAULT 'client',
                plan_id INT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                is_trial TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Setup Inicial do SuperAdmin se não existir
        $stmtUsers = $pdo->query("SELECT COUNT(*) FROM saas_users WHERE role = 'superadmin'");
        if ($stmtUsers->fetchColumn() == 0) {
            $hash = password_hash('tads20122', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO saas_users (role, email, password_hash) VALUES ('superadmin', 'admin@front18.com', '$hash')");
        }

        // Setup Inicial do Cliente Demo (Para a Landing Page de demonstração funcionar out-of-the-box)
        $stmtDemoClient = $pdo->query("SELECT id FROM saas_users WHERE email = 'demo@front18.com'");
        if (!$stmtDemoClient->fetchColumn()) {
            $hash = password_hash('demo123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO saas_users (role, plan_id, email, password_hash) VALUES ('client', 2, 'demo@front18.com', '$hash')");
            $demoId = $pdo->lastInsertId();
            
            // Atrela o domínio democliente.test com a mesma chave mestra usada nos HTMLs didáticos
            $stmt = $pdo->prepare("INSERT INTO saas_origins (user_id, domain, api_key, protection_level, anti_scraping, seo_safe, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$demoId, 'democliente.test', 'SaaS_XXXX_SuperSecretKey_2026', 3, 1, 1, 1]);
        }

        // Autoupdate Migration Silencioso (Para adicionar nas tabelas que o usuário acaba de criar localmente na call passada)
        $columnsAccess = [
            "prev_hash VARCHAR(64) NULL", "current_hash VARCHAR(64) NULL", "server_signature VARCHAR(64) NULL", 
            "terms_version VARCHAR(20) NULL", "terms_hash VARCHAR(64) NULL", "client_id INT NOT NULL DEFAULT 0", "key_version VARCHAR(10) NULL",
            "ai_estimated_age INT NULL", "ai_confidence_score DECIMAL(5,2) NULL"
        ];
        foreach ($columnsAccess as $col) {
            $colName = explode(" ", $col)[0];
            try { $pdo->exec("ALTER TABLE access_logs ADD COLUMN $col"); } catch (\PDOException $e) {}
        }
        
        // Autoupdate Plans Permissions
        $columnsPlans = ["allowed_level INT DEFAULT 2", "has_seo_safe TINYINT(1) DEFAULT 1", "has_anti_scraping TINYINT(1) DEFAULT 0", "is_featured TINYINT(1) DEFAULT 0"];
        foreach ($columnsPlans as $col) {
            $colName = explode(" ", $col)[0];
            try{ $pdo->exec("ALTER TABLE plans ADD COLUMN $col"); } catch(PDOException $e) {}
        }
        
        // Autoupdate Users (plan_id, is_trial)
        try{ $pdo->exec("ALTER TABLE saas_users ADD COLUMN plan_id INT NULL"); } catch(PDOException $e) {}
        try{ $pdo->exec("ALTER TABLE saas_users ADD COLUMN is_trial TINYINT(1) DEFAULT 1"); } catch(PDOException $e) {}

        $columnsOrigins = [
            "api_key VARCHAR(100) UNIQUE NULL", "user_id INT NULL", "terms_url VARCHAR(255) NULL", "privacy_url VARCHAR(255) NULL", "deny_url VARCHAR(255) NULL",
            "protection_level INT DEFAULT 1", "anti_scraping TINYINT(1) DEFAULT 0", "seo_safe TINYINT(1) DEFAULT 0", "is_active TINYINT(1) DEFAULT 1",
            "quota_exceeded_at DATETIME NULL", "server_validation_active TINYINT(1) DEFAULT 1", "age_estimation_active TINYINT(1) DEFAULT 0",
            "display_mode VARCHAR(20) DEFAULT 'global_lock'", "color_primary VARCHAR(20) DEFAULT '#6366f1'", "color_bg VARCHAR(20) DEFAULT '#0f172a'",
            "color_text VARCHAR(20) DEFAULT '#f8fafc'"
        ];
        foreach ($columnsOrigins as $col) {
            $colName = explode(" ", $col)[0];
            try { $pdo->exec("ALTER TABLE saas_origins ADD COLUMN $col"); } catch (\PDOException $e) {}
        }
    }
}
