USE spk_supplier;

INSERT INTO users (nama_user, username, password, level)
SELECT 'Administrator', 'admin', 'admin123', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'admin'
);

INSERT INTO users (nama_user, username, password, level)
SELECT 'Pimpinan', 'pimpinan', 'pimpinan123', 'pimpinan'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'pimpinan'
);

UPDATE users
SET password = 'admin123'
WHERE username = 'admin';

UPDATE users
SET password = 'pimpinan123'
WHERE username = 'pimpinan';
