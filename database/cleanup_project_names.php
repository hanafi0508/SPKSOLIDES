<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function findProjectId(mysqli $conn, string $projectName): ?int
{
    $stmt = mysqli_prepare($conn, "
        SELECT id_proyek
        FROM proyek
        WHERE LOWER(TRIM(nama_proyek)) = LOWER(TRIM(?))
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "s", $projectName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row ? (int) $row['id_proyek'] : null;
}

function ensureProject(mysqli $conn, string $projectName): int
{
    $projectId = findProjectId($conn, $projectName);
    if ($projectId !== null) {
        return $projectId;
    }

    $description = 'Normalisasi proyek dari import data.xlsx';
    $stmt = mysqli_prepare($conn, "
        INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, keterangan)
        VALUES (?, ?, NULL, NULL, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sss", $projectName, $projectName, $description);
    mysqli_stmt_execute($stmt);

    return (int) mysqli_insert_id($conn);
}

function copyAlternatifLinks(mysqli $conn, int $fromProjectId, int $toProjectId): void
{
    $stmt = mysqli_prepare($conn, "SELECT id_supplier FROM alternatif WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $fromProjectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $insertStmt = mysqli_prepare($conn, "
        INSERT INTO alternatif (id_proyek, id_supplier)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE id_supplier = VALUES(id_supplier)
    ");

    while ($row = mysqli_fetch_assoc($result)) {
        $supplierId = (int) $row['id_supplier'];
        mysqli_stmt_bind_param($insertStmt, "ii", $toProjectId, $supplierId);
        mysqli_stmt_execute($insertStmt);
    }
}

function deleteProjectIfExists(mysqli $conn, string $projectName): void
{
    $projectId = findProjectId($conn, $projectName);
    if ($projectId === null) {
        return;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM proyek WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
}

mysqli_begin_transaction($conn);

try {
    $rumahSukaCitaId = findProjectId($conn, 'Rumah Suka Cita');
    if ($rumahSukaCitaId !== null) {
        $targetId = ensureProject($conn, 'Rumah Sukacita');
        copyAlternatifLinks($conn, $rumahSukaCitaId, $targetId);
        deleteProjectIfExists($conn, 'Rumah Suka Cita');
    }

    $cibuburId = findProjectId($conn, 'Cibubur');
    if ($cibuburId !== null) {
        $targetId = ensureProject($conn, 'Citaville Cibubur');
        copyAlternatifLinks($conn, $cibuburId, $targetId);
        deleteProjectIfExists($conn, 'Cibubur');
    }

    $warehouseAliasId = findProjectId($conn, 'Cikarang (Warehouse Delta)');
    if ($warehouseAliasId !== null) {
        $targetId = ensureProject($conn, 'Warehouse Delta');
        copyAlternatifLinks($conn, $warehouseAliasId, $targetId);
        deleteProjectIfExists($conn, 'Cikarang (Warehouse Delta)');
    }

    $groupProjectId = findProjectId($conn, 'Jabodetabek (Citaville Cikarang, Cibubur)');
    if ($groupProjectId !== null) {
        $cikarangId = ensureProject($conn, 'Citaville Cikarang');
        $cibuburTargetId = ensureProject($conn, 'Citaville Cibubur');
        copyAlternatifLinks($conn, $groupProjectId, $cikarangId);
        copyAlternatifLinks($conn, $groupProjectId, $cibuburTargetId);
        deleteProjectIfExists($conn, 'Jabodetabek (Citaville Cikarang, Cibubur)');
    }

    mysqli_commit($conn);
    echo "Cleanup nama proyek selesai.\n";
} catch (Throwable $th) {
    mysqli_rollback($conn);
    fwrite(STDERR, "Cleanup proyek gagal: " . $th->getMessage() . PHP_EOL);
    exit(1);
}
