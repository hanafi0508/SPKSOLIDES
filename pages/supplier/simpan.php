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

$id      = (int) ($_POST['id'] ?? 0);
$nama    = trim($_POST['nama'] ?? '');
$tipe    = $_POST['tipe'] ?? 'barang';
$telp    = trim($_POST['telepon'] ?? '');
$material = trim($_POST['material'] ?? '');
$status  = $_POST['status'] ?? 'aktif';
$alamat  = '';

if ($nama === '') {
    redirError("Nama supplier wajib diisi");
}
if (!in_array($tipe, ['barang', 'jasa'], true)) {
    redirError("Tipe supplier tidak valid");
}
if ($material === '') {
    redirError("Barang / jasa wajib diisi");
}
if (!in_array($status, ['aktif', 'tidak_aktif'], true)) {
    redirError("Status supplier tidak valid");
}

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "
        UPDATE supplier
        SET nama_supplier = ?, tipe_supplier = ?, alamat = ?, no_telepon = ?, jenis_material = ?, status = ?
        WHERE id_supplier = ?
    ");
    mysqli_stmt_bind_param($stmt, "ssssssi", $nama, $tipe, $alamat, $telp, $material, $status, $id);
} else {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO supplier (nama_supplier, tipe_supplier, alamat, no_telepon, jenis_material, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssssss", $nama, $tipe, $alamat, $telp, $material, $status);
}
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;