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
('Administrator', 'admin', 'admin123', 'admin'),
('Pimpinan', 'pimpinan', 'pimpinan123', 'pimpinan');

CREATE TABLE proyek (
    id_proyek INT AUTO_INCREMENT PRIMARY KEY,
    nama_proyek VARCHAR(150),
    lokasi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

CREATE TABLE kriteria (
    id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
    kode_kriteria VARCHAR(10),
    nama_kriteria VARCHAR(100),
    jenis_kriteria ENUM('benefit','cost'),
    deskripsi TEXT
);

CREATE TABLE skala_fuzzy (
    id_skala INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10),
    keterangan VARCHAR(100),
    nilai_l DECIMAL(10,6),
    nilai_m DECIMAL(10,6),
    nilai_u DECIMAL(10,6)
);

CREATE TABLE alternatif (
    id_alternatif INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_supplier INT,
    UNIQUE KEY unik_alternatif_proyek_supplier (id_proyek, id_supplier),
    CONSTRAINT fk_alternatif_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_alternatif_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE penilaian_supplier (
    id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_alternatif INT,
    id_kriteria INT,
    nilai DECIMAL(10,6),
    UNIQUE KEY unik_penilaian_proyek_alt_kriteria (id_proyek, id_alternatif, id_kriteria),
    CONSTRAINT fk_penilaian_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_penilaian_alternatif FOREIGN KEY (id_alternatif) REFERENCES alternatif(id_alternatif) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_penilaian_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE perbandingan_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria_1 INT,
    id_kriteria_2 INT,
    nilai DECIMAL(10,6),
    UNIQUE KEY unik_perbandingan_ahp (id_proyek, id_kriteria_1, id_kriteria_2),
    CONSTRAINT fk_perbandingan_ahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_ahp_kriteria_1 FOREIGN KEY (id_kriteria_1) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_ahp_kriteria_2 FOREIGN KEY (id_kriteria_2) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE bobot_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria INT,
    bobot DECIMAL(10,6),
    lambda_value DECIMAL(10,6),
    ci DECIMAL(10,6),
    cr DECIMAL(10,6),
    status_konsistensi VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_bobot_ahp (id_proyek, id_kriteria),
    CONSTRAINT fk_bobot_ahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bobot_ahp_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE perbandingan_fahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria_1 INT,
    id_kriteria_2 INT,
    id_skala INT,
    nilai_l DECIMAL(10,6),
    nilai_m DECIMAL(10,6),
    nilai_u DECIMAL(10,6),
    UNIQUE KEY unik_perbandingan_fahp (id_proyek, id_kriteria_1, id_kriteria_2),
    CONSTRAINT fk_perbandingan_fahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_fahp_kriteria_1 FOREIGN KEY (id_kriteria_1) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_fahp_kriteria_2 FOREIGN KEY (id_kriteria_2) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perbandingan_fahp_skala FOREIGN KEY (id_skala) REFERENCES skala_fuzzy(id_skala) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE bobot_fahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria INT,
    bobot DECIMAL(10,6),
    UNIQUE KEY unik_bobot_fahp (id_proyek, id_kriteria),
    CONSTRAINT fk_bobot_fahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bobot_fahp_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE hasil_perhitungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_alternatif INT,
    metode ENUM('AHP','F-AHP'),
    nilai DECIMAL(10,6),
    ranking INT,
    UNIQUE KEY unik_hasil_perhitungan (id_proyek, id_alternatif, metode),
    CONSTRAINT fk_hasil_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hasil_alternatif FOREIGN KEY (id_alternatif) REFERENCES alternatif(id_alternatif) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u) VALUES
('SP', 'Sama Penting', 1.000000, 1.000000, 1.000000),
('SD', 'Sedikit Diutamakan', 1.000000, 2.000000, 3.000000),
('C', 'Cukup Diutamakan', 2.000000, 3.000000, 4.000000),
('L', 'Lebih Diutamakan', 3.000000, 4.000000, 5.000000),
('SL', 'Sangat Lebih Diutamakan', 4.000000, 5.000000, 6.000000),
('M', 'Mutlak Lebih Diutamakan', 5.000000, 6.000000, 7.000000),
('MM', 'Mendekati Mutlak', 6.000000, 7.000000, 8.000000),
('AM', 'Antara Mendekati dan Mutlak', 7.000000, 8.000000, 9.000000),
('EM', 'Ekstrem Mutlak', 8.000000, 9.000000, 9.000000);
