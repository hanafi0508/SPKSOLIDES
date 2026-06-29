<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id_proyek = (int) $_POST['id_proyek'];
if ($id_proyek <= 0 || empty($_POST['nilai']) || !is_array($_POST['nilai'])) {
    header("Location: index.php?proyek=$id_proyek&error=1");
    exit;
}

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO penilaian_supplier (id_proyek, id_alternatif, id_kriteria, nilai)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
    ");

    foreach ($_POST['nilai'] as $id_alt => $k) {
        foreach ($k as $id_krit => $val) {
            if ($val === '' || !is_numeric($val)) {
                throw new RuntimeException('Nilai tidak valid.');
            }

            $idAlternatif = (int) $id_alt;
            $idKriteria = (int) $id_krit;
            $nilaiInput = (float) $val;

            if ($nilaiInput < 1 || $nilaiInput > 10 || floor($nilaiInput) != $nilaiInput) {
                throw new RuntimeException('Nilai harus 1 sampai 10.');
            }

            mysqli_stmt_bind_param($stmt, "iiid", $id_proyek, $idAlternatif, $idKriteria, $nilaiInput);
            mysqli_stmt_execute($stmt);
        }
    }

    mysqli_commit($conn);
    header("Location: index.php?proyek=$id_proyek&success=1");
    exit;
} catch (Throwable $th) {
    mysqli_rollback($conn);
    header("Location: index.php?proyek=$id_proyek&error=1");
    exit;
}
