
CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100),
 email VARCHAR(100),
 password VARCHAR(255)
);

CREATE TABLE ai_providers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(50),
 type VARCHAR(50),
 base_url TEXT,
 status VARCHAR(20),
 api_key TEXT
);

CREATE TABLE ai_requests (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT,
 provider VARCHAR(50),
 prompt TEXT,
 response TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
