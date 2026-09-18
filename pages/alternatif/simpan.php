<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

function redirError(string $message): void
{
    header("Location: index.php?error=" . urlencode($message));
    exit;
}

$id_proyek   = (int) ($_POST['proyek'] ?? 0);
$id_supplier = (int) ($_POST['supplier'] ?? 0);

if ($id_proyek <= 0) {
    redirError("Proyek tidak valid");
}
if ($id_supplier <= 0) {
    redirError("Pilih supplier terlebih dahulu");
}

$stmt = mysqli_prepare($conn, "
    SELECT id_alternatif FROM alternatif
    WHERE id_proyek = ? AND id_supplier = ?
");
mysqli_stmt_bind_param($stmt, "ii", $id_proyek, $id_supplier);
mysqli_stmt_execute($stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
    redirError("Supplier sudah ada di proyek ini");
}

$stmt = mysqli_prepare($conn, "
    INSERT INTO alternatif (id_proyek, id_supplier) VALUES (?, ?)
");
mysqli_stmt_bind_param($stmt, "ii", $id_proyek, $id_supplier);
mysqli_stmt_execute($stmt);

header("Location: index.php?proyek=$id_proyek");
exit;