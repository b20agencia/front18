-- ==========================================================
-- Instalação Manual Front18 MasterHub (cPanel / phpMyAdmin)
-- ==========================================================
-- Nota: O próprio sistema PHP cria essas tabelas automaticamente
-- se o banco estiver vazio. Este arquivo é apenas um backup 
-- de segurança para quem prefere instalar via phpMyAdmin.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Usuários do SaaS (Clientes Lojistas e o SuperAdmin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saas_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) DEFAULT 'client',
  `plan_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_trial` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere o SuperAdmin Padrão (Email: admin@front18.com / Senha: tads20122)
INSERT INTO `saas_users` (`id`, `role`, `plan_id`, `email`, `password_hash`, `is_trial`) VALUES
(1, 'superadmin', NULL, 'admin@front18.com', '$2y$10$f.t6RqNiT.D2kDRB2xf.lOemx1lGsebpXjiBXxps2s97Qch.y6ctC', 1);


-- --------------------------------------------------------
-- 2. Planos Comerciais B2B (Módulos de Defesa e Limites)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_domains` int(11) NOT NULL,
  `max_requests_per_month` bigint(20) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `allowed_level` int(11) DEFAULT 1,
  `has_seo_safe` tinyint(1) DEFAULT 0,
  `has_anti_scraping` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere os planos base da Plataforma
INSERT INTO `plans` (`name`, `price`, `max_domains`, `max_requests_per_month`, `is_featured`, `allowed_level`, `has_seo_safe`, `has_anti_scraping`) VALUES
('Starter Trial', 0.00, 1, 200, 0, 1, 0, 0),
('SaaS Start Ouro', 149.90, 1, 150000, 1, 2, 1, 0),
('Corporativo Edge', 399.90, 3, 500000, 0, 3, 1, 1);


-- --------------------------------------------------------
-- 3. Domínios e Origens (Onde a SDK está instalada)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saas_origins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `api_key` varchar(100) DEFAULT NULL,
  `terms_url` varchar(255) DEFAULT NULL,
  `privacy_url` varchar(255) DEFAULT NULL,
  `pry_deny_url` varchar(255) DEFAULT NULL,
  `deny_url` varchar(255) DEFAULT NULL,
  `privacy_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`privacy_config`)),
  `modal_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modal_config`)),
  `protection_level` int(11) DEFAULT 1,
  `anti_scraping` tinyint(1) DEFAULT 0,
  `seo_safe` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `quota_exceeded_at` datetime DEFAULT NULL,
  `server_validation_active` tinyint(1) DEFAULT 1,
  `age_estimation_active` tinyint(1) DEFAULT 0,
  `display_mode` varchar(20) DEFAULT 'global_lock',
  `color_primary` varchar(20) DEFAULT '#6366f1',
  `color_bg` varchar(20) DEFAULT '#0f172a',
  `color_text` varchar(20) DEFAULT '#f8fafc',
  `wp_url` varchar(255) DEFAULT NULL,
  `wp_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`wp_rules`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- 4. Logs de Acesso da SDK (Telemetria do WAF)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `access_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL DEFAULT 0,
  `siteOrigin` varchar(150) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `action` varchar(50) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `prev_hash` varchar(64) DEFAULT NULL,
  `current_hash` varchar(64) DEFAULT NULL,
  `server_signature` varchar(64) DEFAULT NULL,
  `key_version` varchar(10) DEFAULT NULL,
  `terms_version` varchar(20) DEFAULT NULL,
  `terms_hash` varchar(64) DEFAULT NULL,
  `ai_estimated_age` int(11) DEFAULT NULL,
  `ai_confidence_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- 5. Atividades Suspeitas (Bloqueios Severos L7)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `suspicious_activity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `ip_masked` varchar(50) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- 6. Ticket do Canal DPO de Denúncia Jurídica (LGPD)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saas_dpo_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `reporter_name` varchar(255) DEFAULT NULL,
  `reporter_email` varchar(255) DEFAULT NULL,
  `reporter_phone` varchar(50) DEFAULT NULL,
  `reporter_role` varchar(100) DEFAULT NULL,
  `violation_type` varchar(100) DEFAULT NULL,
  `content_url` varchar(500) DEFAULT NULL,
  `report_message` text NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- 7. Audit Trail Interno do Sistema
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL DEFAULT 0,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- 8. Tabela Local Antiga de Conteúdo Isolado 
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `protected_content` (
  `id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `html_content` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
