USE spk_supplier;

UPDATE skala_fuzzy SET keterangan = '1 - Sama penting' WHERE kode = 'SP';
UPDATE skala_fuzzy SET keterangan = '2 - Sedikit lebih penting' WHERE kode = 'SD';
UPDATE skala_fuzzy SET keterangan = '3 - Cukup lebih penting' WHERE kode = 'C';
UPDATE skala_fuzzy SET keterangan = '4 - Lebih penting' WHERE kode = 'L';
UPDATE skala_fuzzy SET keterangan = '5 - Sangat lebih penting' WHERE kode = 'SL';
UPDATE skala_fuzzy SET keterangan = '6 - Dominan' WHERE kode = 'M';
UPDATE skala_fuzzy SET keterangan = '7 - Sangat dominan' WHERE kode = 'MM';
UPDATE skala_fuzzy SET keterangan = '8 - Hampir mutlak' WHERE kode = 'AM';
UPDATE skala_fuzzy SET keterangan = '9 - Mutlak' WHERE kode = 'EM';
