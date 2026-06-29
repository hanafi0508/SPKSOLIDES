USE spk_supplier;

ALTER TABLE supplier
ADD COLUMN tipe_supplier ENUM('barang', 'jasa') NOT NULL DEFAULT 'barang' AFTER nama_supplier;
