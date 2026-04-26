INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u) VALUES
('1', 'Sama Penting', 1, 1, 1),
('3', 'Sedikit Lebih Penting', 1, 3, 5),
('5', 'Lebih Penting', 3, 5, 7),
('7', 'Sangat Penting', 5, 7, 9),
('9', 'Mutlak Lebih Penting', 7, 9, 9);

INSERT INTO users (nama_user, username, password, level)
VALUES ('Admin', 'admin', 'HASIL_HASH', 'admin');

INSERT INTO users (nama_user, username, password, level)
VALUES ('Pimpinan', 'pimpinan', 'HASIL_HASH', 'pimpinan');
