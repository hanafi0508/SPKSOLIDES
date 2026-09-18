<?php
/**
 * Migrasi skema lama -> skema AHP per proyek.
 *
 * Idempotent dan aman dijalankan ulang:
 * 1. Backup tabel lama menjadi *_legacy (data tidak pernah dihapus).
 * 2. Tambah kolom baru yang dibutuhkan aplikasi (kriteria.jenis_kriteria,
 *    supplier.tipe_supplier).
 * 3. Bangun ulang tabel baru (alternatif, penilaian_supplier,
 *    perbandingan_ahp, bobot_ahp, hasil_perhitungan) sesuai skema init.sql,
 *    lalu isi dari data *_legacy (disalin ke setiap proyek).
 * 4. Hapus tabel sisa yang tidak dipakai aplikasi (login_attempts).
 */

require_once __DIR__ . '/../config/database.php';

$legacyTables = [
    'penilaian_supplier',
    'perbandingan_ahp',
    'bobot_ahp',
    'proyek_supplier',
    'hasil_ranking',
];

function tableExists(mysqli $conn, string $table): bool
{
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function dropForeignKeys(mysqli $conn, string $table): void
{
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '$table'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

    $constraints = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $constraints[] = $row['CONSTRAINT_NAME'];
    }

    foreach ($constraints as $name) {
        $name = mysqli_real_escape_string($conn, $name);
        mysqli_query($conn, "ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
    }
}

echo "== Migrasi skema spk_supplier ==\n";

$isNewSchema = tableExists($conn, 'alternatif') && !tableExists($conn, 'proyek_supplier_legacy');
if ($isNewSchema) {
    echo "Skema sudah baru. Tidak ada yang perlu dimigrasi.\n";
    exit(0);
}

try {
    // 1. Backup tabel lama (hanya bila belum pernah di-backup)
    foreach ($legacyTables as $table) {
        $legacy = $table . '_legacy';
        if (tableExists($conn, $table) && !tableExists($conn, $legacy)) {
            mysqli_query($conn, "RENAME TABLE `$table` TO `$legacy`");
            echo "backup  : $table -> $legacy\n";
        }
    }

    // Lepas FK pada tabel backup agar nama constraint bisa dipakai tabel baru
    foreach ($legacyTables as $table) {
        $legacy = $table . '_legacy';
        if (tableExists($conn, $legacy)) {
            dropForeignKeys($conn, $legacy);
        }
    }

    // 2. Kolom baru (idempotent)
    $res = mysqli_query($conn, "SHOW COLUMNS FROM kriteria LIKE 'jenis_kriteria'");
    if (!$res || mysqli_num_rows($res) === 0) {
        mysqli_query($conn, "ALTER TABLE kriteria
            ADD COLUMN jenis_kriteria ENUM('benefit','cost') NOT NULL DEFAULT 'benefit' AFTER nama_kriteria");
        echo "alter   : kriteria.jenis_kriteria ditambahkan (default benefit)\n";
    }

    $res = mysqli_query($conn, "SHOW COLUMNS FROM supplier LIKE 'tipe_supplier'");
    if (!$res || mysqli_num_rows($res) === 0) {
        mysqli_query($conn, "ALTER TABLE supplier
            ADD COLUMN tipe_supplier ENUM('barang','jasa') NOT NULL DEFAULT 'barang' AFTER nama_supplier");
        echo "alter   : supplier.tipe_supplier ditambahkan (default barang)\n";
    }

    // 3. Drop + create ulang tabel baru (urutan drop: anak dulu dari orang tua)
    $newTables = ['penilaian_supplier', 'perbandingan_ahp', 'bobot_ahp', 'hasil_perhitungan', 'alternatif'];
    foreach ($newTables as $table) {
        if (tableExists($conn, $table)) {
            mysqli_query($conn, "DROP TABLE `$table`");
        }
    }

    mysqli_query($conn, "
        CREATE TABLE alternatif (
            id_alternatif INT AUTO_INCREMENT PRIMARY KEY,
            id_proyek INT,
            id_supplier INT,
            UNIQUE KEY unik_alternatif_proyek_supplier (id_proyek, id_supplier),
            CONSTRAINT fk_alternatif_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_alternatif_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE
        )
    ");
    mysqli_query($conn, "
        INSERT INTO alternatif (id_alternatif, id_proyek, id_supplier)
        SELECT id, id_proyek, id_supplier FROM proyek_supplier_legacy
    ");

    mysqli_query($conn, "
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
        )
    ");
    mysqli_query($conn, "
        INSERT INTO penilaian_supplier (id_proyek, id_alternatif, id_kriteria, nilai)
        SELECT ps.id_proyek, ps.id, pl.id_kriteria, pl.skor
        FROM proyek_supplier_legacy ps
        JOIN penilaian_supplier_legacy pl ON pl.id_supplier = ps.id_supplier
    ");

    mysqli_query($conn, "
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
        )
    ");
    mysqli_query($conn, "
        INSERT INTO perbandingan_ahp (id_proyek, id_kriteria_1, id_kriteria_2, nilai)
        SELECT p.id_proyek, pl.id_kriteria_1, pl.id_kriteria_2, pl.nilai
        FROM proyek p
        JOIN perbandingan_ahp_legacy pl
    ");

    mysqli_query($conn, "
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
        )
    ");
    mysqli_query($conn, "
        INSERT INTO bobot_ahp (id_proyek, id_kriteria, bobot, lambda_value, ci, cr, status_konsistensi, created_at)
        SELECT p.id_proyek, bl.id_kriteria, bl.bobot, bl.lambda_value, bl.ci, bl.cr, bl.status_konsistensi, bl.created_at
        FROM proyek p
        JOIN bobot_ahp_legacy bl
    ");

    mysqli_query($conn, "
        CREATE TABLE hasil_perhitungan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_proyek INT,
            id_alternatif INT,
            metode ENUM('AHP'),
            nilai DECIMAL(10,6),
            ranking INT,
            UNIQUE KEY unik_hasil_perhitungan (id_proyek, id_alternatif, metode),
            CONSTRAINT fk_hasil_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_hasil_alternatif FOREIGN KEY (id_alternatif) REFERENCES alternatif(id_alternatif) ON DELETE CASCADE ON UPDATE CASCADE
        )
    ");

    // 4. Hapus tabel sisa yang tidak dipakai aplikasi
    if (tableExists($conn, 'login_attempts')) {
        mysqli_query($conn, "DROP TABLE login_attempts");
        echo "hapus   : login_attempts (tidak dipakai aplikasi)\n";
    }

    echo "\n== Ringkasan hasil ==\n";
    foreach (['users', 'proyek', 'supplier', 'kriteria', 'alternatif', 'penilaian_supplier', 'perbandingan_ahp', 'bobot_ahp', 'hasil_perhitungan'] as $table) {
        $total = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$table`"))['c'] ?? 0);
        echo sprintf("%-20s %d baris\n", $table, $total);
    }
    echo "\nMigrasi selesai. Tabel lama tersimpan sebagai *_legacy (bisa dihapus manual bila sudah yakin).\n";
    echo "Catatan: ranking akhir dihitung ulang melalui menu Ranking -> Proses & Simpan Ranking.\n";
} catch (Throwable $th) {
    fwrite(STDERR, "Migrasi gagal di tengah jalan (skrip idempotent, aman dijalankan ulang): " . $th->getMessage() . "\n");
    exit(1);
}