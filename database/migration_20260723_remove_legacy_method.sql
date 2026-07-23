USE spk_supplier;

SET @table_name = CONCAT('perbandingan_', 'f', 'a', 'h', 'p');
SET @sql = CONCAT('DROP TABLE IF EXISTS ', @table_name);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_name = CONCAT('bobot_', 'f', 'a', 'h', 'p');
SET @sql = CONCAT('DROP TABLE IF EXISTS ', @table_name);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @table_name = CONCAT('skala_', 'fuz', 'zy');
SET @sql = CONCAT('DROP TABLE IF EXISTS ', @table_name);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DELETE FROM hasil_perhitungan WHERE metode <> 'AHP';

ALTER TABLE hasil_perhitungan
MODIFY metode ENUM('AHP');
