USE spk_supplier;

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SP', '1 - Sama penting', 1.000000, 1.000000, 1.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SP');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SD', '2 - Sedikit lebih penting', 1.000000, 2.000000, 3.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SD');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'C', '3 - Cukup lebih penting', 2.000000, 3.000000, 4.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'C');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'L', '4 - Lebih penting', 3.000000, 4.000000, 5.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'L');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SL', '5 - Sangat lebih penting', 4.000000, 5.000000, 6.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SL');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'M', '6 - Dominan', 5.000000, 6.000000, 7.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'M');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'MM', '7 - Sangat dominan', 6.000000, 7.000000, 8.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'MM');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'AM', '8 - Hampir mutlak', 7.000000, 8.000000, 9.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'AM');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'EM', '9 - Mutlak', 8.000000, 9.000000, 9.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'EM');
