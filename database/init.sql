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
    id_supplier INT
);

CREATE TABLE penilaian_supplier (
    id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_alternatif INT,
    id_kriteria INT,
    nilai DECIMAL(10,6)
);

CREATE TABLE perbandingan_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria_1 INT,
    id_kriteria_2 INT,
    nilai DECIMAL(10,6)
);

CREATE TABLE bobot_ahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria INT,
    bobot DECIMAL(10,6)
);

CREATE TABLE perbandingan_fahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria_1 INT,
    id_kriteria_2 INT,
    id_skala INT,
    nilai_l DECIMAL(10,6),
    nilai_m DECIMAL(10,6),
    nilai_u DECIMAL(10,6)
);

CREATE TABLE bobot_fahp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_kriteria INT,
    bobot DECIMAL(10,6)
);

CREATE TABLE hasil_perhitungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT,
    id_alternatif INT,
    metode ENUM('AHP','F-AHP'),
    nilai DECIMAL(10,6),
    ranking INT
);
