-- AgeGate Pro - Sistema de Banco de Dados Oficial
-- Desenvolvido para Alta Escalabilidade (SaaS)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    plan_id INT DEFAULT NULL,
    role VARCHAR(50) DEFAULT 'client',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    max_domains INT NOT NULL,
    max_requests_per_month BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain_name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('active', 'paused', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS domain_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    block_vpn BOOLEAN DEFAULT FALSE,
    allowed_countries JSON,
    protection_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    terms_url VARCHAR(255) DEFAULT NULL,
    privacy_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    event_type ENUM('allowed', 'blocked', 'suspicious') NOT NULL,
    ip_masked VARCHAR(50) NOT NULL,
    country VARCHAR(3) DEFAULT NULL,
    user_agent_hash VARCHAR(64) NOT NULL,
    session_id VARCHAR(100) NOT NULL,
    contract_hash VARCHAR(100) DEFAULT NULL,
    is_legit BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS hash_chain (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    log_id BIGINT NOT NULL,
    previous_hash VARCHAR(64) NOT NULL,
    current_hash VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (log_id) REFERENCES audit_logs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS suspicious_activity (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    ip_masked VARCHAR(50) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    hash_signature VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('active', 'canceled', 'overdue') DEFAULT 'active',
    current_period_end DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
);

-- Índices de Performance
CREATE INDEX idx_domain_id_audit_logs ON audit_logs(domain_id);
CREATE INDEX idx_created_at_audit_logs ON audit_logs(created_at);
