USE spk_supplier;

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SP', 'Sama Penting', 1.000000, 1.000000, 1.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SP');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SD', 'Sedikit Diutamakan', 1.000000, 2.000000, 3.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SD');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'C', 'Cukup Diutamakan', 2.000000, 3.000000, 4.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'C');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'L', 'Lebih Diutamakan', 3.000000, 4.000000, 5.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'L');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'SL', 'Sangat Lebih Diutamakan', 4.000000, 5.000000, 6.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'SL');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'M', 'Mutlak Lebih Diutamakan', 5.000000, 6.000000, 7.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'M');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'MM', 'Mendekati Mutlak', 6.000000, 7.000000, 8.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'MM');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'AM', 'Antara Mendekati dan Mutlak', 7.000000, 8.000000, 9.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'AM');

INSERT INTO skala_fuzzy (kode, keterangan, nilai_l, nilai_m, nilai_u)
SELECT 'EM', 'Ekstrem Mutlak', 8.000000, 9.000000, 9.000000
WHERE NOT EXISTS (SELECT 1 FROM skala_fuzzy WHERE kode = 'EM');
