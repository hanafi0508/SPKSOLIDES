DROP DATABASE IF EXISTS spk_supplier;
CREATE DATABASE spk_supplier;
USE spk_supplier;

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_user VARCHAR(100),
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    level ENUM('admin','pimpinan'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (nama_user, username, password, level) VALUES
('Administrator', 'ambatukam', '$2y$12$SqAxP8t7z49qoOA3JLA.x.6IJcXjHoOvnMBDzHwSP.rAG0dNWyAZ6', 'admin'),
('Pimpinan', 'rusdi', '$2y$12$1IM9xjz9Fv09l/v2wsGk9eR4JqnTvKcN83m8WXyUNITW28/H/dgLm', 'pimpinan');

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    ip_address VARCHAR(45),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_login_user_time (username, attempted_at),
    KEY idx_login_ip_time (ip_address, attempted_at)
);

CREATE TABLE kriteria (
    id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
    kode_kriteria VARCHAR(10),
    nama_kriteria VARCHAR(100),
    deskripsi TEXT
);

CREATE TABLE supplier (
    id_supplier INT AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(150),
    alamat TEXT,
    no_telepon VARCHAR(30),
    email VARCHAR(100),
    jenis_material VARCHAR(100),
    status ENUM('aktif','tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE proyek (
    id_proyek INT AUTO_INCREMENT PRIMARY KEY,
    nama_proyek VARCHAR(150),
    lokasi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE proyek_supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_supplier INT,
    UNIQUE KEY unik_proyek_supplier (id_proyek, id_supplier),
    CONSTRAINT fk_proyek_supplier_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_proyek_supplier_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE perbandingan_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria_1 INT,
    id_kriteria_2 INT,
    nilai DECIMAL(10,6),
    UNIQUE KEY unik_perbandingan_ahp (id_kriteria_1, id_kriteria_2),
    CONSTRAINT fk_perbandingan_ahp_kriteria_1 FOREIGN KEY (id_kriteria_1) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_ahp_kriteria_2 FOREIGN KEY (id_kriteria_2) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE bobot_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria INT,
    bobot DECIMAL(10,6),
    lambda_value DECIMAL(10,6),
    ci DECIMAL(10,6),
    cr DECIMAL(10,6),
    status_konsistensi VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_bobot_ahp (id_kriteria),
    CONSTRAINT fk_bobot_ahp_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE penilaian_supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria INT,
    id_supplier INT,
    skor TINYINT UNSIGNED NOT NULL,
    UNIQUE KEY unik_penilaian_supplier (id_kriteria, id_supplier),
    CONSTRAINT fk_penilaian_supplier_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_penilaian_supplier_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE hasil_ranking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_supplier INT,
    nilai DECIMAL(10,6),
    ranking INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_hasil_ranking (id_supplier),
    CONSTRAINT fk_hasil_ranking_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE
);
