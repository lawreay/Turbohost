-- ==========================================================
-- TWO FACTOR AUTH CODES
-- ==========================================================

CREATE TABLE IF NOT EXISTS two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_two_factor_codes_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================================
-- PROFILE EXTENSIONS: bio, email change, deactivation
-- ==========================================================

DELIMITER $$
DROP PROCEDURE IF EXISTS add_missing_legal_schema$$
CREATE PROCEDURE add_missing_legal_schema()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'bio'
    ) THEN
        ALTER TABLE users ADD COLUMN bio VARCHAR(500) NULL;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'payments'
          AND COLUMN_NAME = 'tx_ref'
    ) THEN
        ALTER TABLE payments ADD COLUMN tx_ref VARCHAR(120) AFTER transaction_id;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'payments'
          AND COLUMN_NAME = 'provider_response'
    ) THEN
        ALTER TABLE payments ADD COLUMN provider_response JSON NULL AFTER tx_ref;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'payments'
          AND COLUMN_NAME = 'verified_at'
    ) THEN
        ALTER TABLE payments ADD COLUMN verified_at DATETIME NULL AFTER provider_response;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'payments'
          AND INDEX_NAME = 'idx_payments_tx_ref'
    ) THEN
        ALTER TABLE payments ADD UNIQUE INDEX idx_payments_tx_ref (tx_ref);
    END IF;
END$$
CALL add_missing_legal_schema()$$
DROP PROCEDURE IF EXISTS add_missing_legal_schema$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS email_change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    new_email VARCHAR(150) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_change_token_hash (token_hash),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS deactivation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_deactivation_token_hash (token_hash),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================================
-- PAYCHANGU PAYMENT INTEGRATION
-- ==========================================================

INSERT INTO platform_settings(setting_key, setting_value, setting_group)
VALUES
('premium_plan_amount', '5000', 'pricing'),
('paychangu_currency', 'MWK', 'pricing'),
('paychangu_public_key', '', 'pricing'),
('paychangu_secret_key', '', 'pricing')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- ==========================================================
-- LEGAL POLICY MANAGEMENT
-- ==========================================================

CREATE TABLE IF NOT EXISTS legal_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_legal_policies_type (policy_type)
);

CREATE TABLE IF NOT EXISTS legal_policy_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_id INT NOT NULL,
    version_label VARCHAR(100) NOT NULL,
    content LONGTEXT NOT NULL,
    published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (policy_id) REFERENCES legal_policies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_policy_acceptances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    policy_id INT NOT NULL,
    version_id INT NOT NULL,
    accepted_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (policy_id) REFERENCES legal_policies(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES legal_policy_versions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS policy_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    policy_id INT NOT NULL,
    version_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (policy_id) REFERENCES legal_policies(id) ON DELETE CASCADE,
    FOREIGN KEY (version_id) REFERENCES legal_policy_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
