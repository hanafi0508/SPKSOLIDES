<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function fetchSupplier(mysqli $conn, int $idSupplier): ?array
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM supplier WHERE id_supplier = ?");
    mysqli_stmt_bind_param($stmt, "i", $idSupplier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row ?: null;
}

function mergeText(string $primary, string $secondary): string
{
    $primary = trim($primary);
    $secondary = trim($secondary);

    if ($primary === '') {
        return $secondary;
    }

    if ($secondary === '' || str_contains(mb_strtolower($primary), mb_strtolower($secondary))) {
        return $primary;
    }

    if (str_contains(mb_strtolower($secondary), mb_strtolower($primary))) {
        return $secondary;
    }

    return $primary . '; ' . $secondary;
}

function reassignAlternatif(mysqli $conn, int $winnerId, int $loserId): void
{
    $stmt = mysqli_prepare($conn, "SELECT id_alternatif, id_proyek FROM alternatif WHERE id_supplier = ?");
    mysqli_stmt_bind_param($stmt, "i", $loserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($alternatif = mysqli_fetch_assoc($result)) {
        $idAlternatif = (int) $alternatif['id_alternatif'];
        $idProyek = (int) $alternatif['id_proyek'];

        $stmtExisting = mysqli_prepare($conn, "
            SELECT id_alternatif
            FROM alternatif
            WHERE id_proyek = ? AND id_supplier = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmtExisting, "ii", $idProyek, $winnerId);
        mysqli_stmt_execute($stmtExisting);
        $existingResult = mysqli_stmt_get_result($stmtExisting);
        $existing = mysqli_fetch_assoc($existingResult);

        if ($existing) {
            $winnerAlternatifId = (int) $existing['id_alternatif'];

            $stmtPenilaianRows = mysqli_prepare($conn, "
                SELECT id_kriteria, nilai
                FROM penilaian_supplier
                WHERE id_alternatif = ?
            ");
            mysqli_stmt_bind_param($stmtPenilaianRows, "i", $idAlternatif);
            mysqli_stmt_execute($stmtPenilaianRows);
            $penilaianRows = mysqli_stmt_get_result($stmtPenilaianRows);

            $stmtPenilaianUpsert = mysqli_prepare($conn, "
                INSERT INTO penilaian_supplier (id_proyek, id_alternatif, id_kriteria, nilai)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
            ");

            while ($penilaian = mysqli_fetch_assoc($penilaianRows)) {
                $idKriteria = (int) $penilaian['id_kriteria'];
                $nilai = (float) $penilaian['nilai'];
                mysqli_stmt_bind_param($stmtPenilaianUpsert, "iiid", $idProyek, $winnerAlternatifId, $idKriteria, $nilai);
                mysqli_stmt_execute($stmtPenilaianUpsert);
            }

            $stmtDeletePenilaian = mysqli_prepare($conn, "DELETE FROM penilaian_supplier WHERE id_alternatif = ?");
            mysqli_stmt_bind_param($stmtDeletePenilaian, "i", $idAlternatif);
            mysqli_stmt_execute($stmtDeletePenilaian);

            $stmtDeleteAlternatif = mysqli_prepare($conn, "DELETE FROM alternatif WHERE id_alternatif = ?");
            mysqli_stmt_bind_param($stmtDeleteAlternatif, "i", $idAlternatif);
            mysqli_stmt_execute($stmtDeleteAlternatif);
            continue;
        }

        $stmtUpdateAlternatif = mysqli_prepare($conn, "UPDATE alternatif SET id_supplier = ? WHERE id_alternatif = ?");
        mysqli_stmt_bind_param($stmtUpdateAlternatif, "ii", $winnerId, $idAlternatif);
        mysqli_stmt_execute($stmtUpdateAlternatif);
    }
}

function mergeSupplier(mysqli $conn, int $winnerId, int $loserId, array $overrides = []): void
{
    $winner = fetchSupplier($conn, $winnerId);
    $loser = fetchSupplier($conn, $loserId);

    if (!$winner || !$loser) {
        return;
    }

    reassignAlternatif($conn, $winnerId, $loserId);

    $nama = $overrides['nama_supplier'] ?? $winner['nama_supplier'];
    $tipe = $overrides['tipe_supplier'] ?? $winner['tipe_supplier'];
    $alamat = $overrides['alamat'] ?? mergeText((string) $winner['alamat'], (string) $loser['alamat']);
    $telepon = $overrides['no_telepon'] ?? ((string) $winner['no_telepon'] !== '' ? $winner['no_telepon'] : $loser['no_telepon']);
    $jenisMaterial = $overrides['jenis_material'] ?? mergeText((string) $winner['jenis_material'], (string) $loser['jenis_material']);
    $status = $overrides['status'] ?? $winner['status'];

    $stmtUpdateWinner = mysqli_prepare($conn, "
        UPDATE supplier
        SET nama_supplier = ?, tipe_supplier = ?, alamat = ?, no_telepon = ?, jenis_material = ?, status = ?
        WHERE id_supplier = ?
    ");
    mysqli_stmt_bind_param($stmtUpdateWinner, "ssssssi", $nama, $tipe, $alamat, $telepon, $jenisMaterial, $status, $winnerId);
    mysqli_stmt_execute($stmtUpdateWinner);

    $stmtDeleteLoser = mysqli_prepare($conn, "DELETE FROM supplier WHERE id_supplier = ?");
    mysqli_stmt_bind_param($stmtDeleteLoser, "i", $loserId);
    mysqli_stmt_execute($stmtDeleteLoser);
}

$rules = [
    [
        'winner' => 88,
        'loser' => 87,
        'overrides' => [
            'nama_supplier' => 'Sahabat Sampora Jaya',
            'jenis_material' => 'Material Alam - Pasir, Spilt, dll',
        ],
    ],
    [
        'winner' => 62,
        'loser' => 49,
        'overrides' => [
            'nama_supplier' => 'PT. Karya Baru Jaya Perkasa',
            'jenis_material' => 'Arsitektur Atap - Atap Baja Ringan; Pasang Baja Ringan Atap',
        ],
    ],
    [
        'winner' => 86,
        'loser' => 85,
        'overrides' => [
            'nama_supplier' => 'Rohman / Komeng',
            'status' => 'tidak_aktif',
        ],
    ],
];

mysqli_begin_transaction($conn);

try {
    foreach ($rules as $rule) {
        mergeSupplier($conn, $rule['winner'], $rule['loser'], $rule['overrides']);
    }

    mysqli_commit($conn);
    echo "Cleanup duplikat supplier selesai.\n";
} catch (Throwable $th) {
    mysqli_rollback($conn);
    fwrite(STDERR, "Cleanup gagal: " . $th->getMessage() . PHP_EOL);
    exit(1);
}
