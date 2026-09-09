-- Migrasi: tabel brute-force login
-- Berlaku pada database yang sudah terlanjur dibuat (tanpa drop).
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    ip_address VARCHAR(45),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_login_user_time (username, attempted_at),
    KEY idx_login_ip_time (ip_address, attempted_at)
);