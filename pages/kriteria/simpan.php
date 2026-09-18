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

$id         = (int) ($_POST['id'] ?? 0);
$kode       = strtoupper(trim($_POST['kode'] ?? ''));
$nama       = trim($_POST['nama'] ?? '');
$jenis      = $_POST['jenis'] ?? '';
$deskripsi  = trim($_POST['deskripsi'] ?? '');

if ($kode === '') {
    redirError("Kode wajib diisi");
}
if ($nama === '') {
    redirError("Nama wajib diisi");
}
if (!in_array($jenis, ['benefit', 'cost'], true)) {
    redirError("Jenis harus benefit atau cost");
}

$stmt = mysqli_prepare($conn, "SELECT id_kriteria FROM kriteria WHERE kode_kriteria = ? AND id_kriteria != ?");
mysqli_stmt_bind_param($stmt, "si", $kode, $id);
mysqli_stmt_execute($stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
    redirError("Kode sudah digunakan");
}

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "
        UPDATE kriteria
        SET kode_kriteria = ?, nama_kriteria = ?, jenis_kriteria = ?, deskripsi = ?
        WHERE id_kriteria = ?
    ");
    mysqli_stmt_bind_param($stmt, "ssssi", $kode, $nama, $jenis, $deskripsi, $id);
} else {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO kriteria (kode_kriteria, nama_kriteria, jenis_kriteria, deskripsi)
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssss", $kode, $nama, $jenis, $deskripsi);
}
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;