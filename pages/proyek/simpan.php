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
$lokasi  = trim($_POST['lokasi'] ?? '');
$mulai   = $_POST['mulai'] ?? '';
$selesai = $_POST['selesai'] ?? '';
$ket     = trim($_POST['keterangan'] ?? '');

if ($nama === '') {
    redirError("Nama proyek wajib diisi");
}
if ($selesai < $mulai) {
    redirError("Tanggal selesai tidak boleh sebelum mulai");
}

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "
        UPDATE proyek
        SET nama_proyek = ?, lokasi = ?, tanggal_mulai = ?, tanggal_selesai = ?, keterangan = ?
        WHERE id_proyek = ?
    ");
    mysqli_stmt_bind_param($stmt, "sssssi", $nama, $lokasi, $mulai, $selesai, $ket, $id);
} else {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, keterangan)
        VALUES (?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sssss", $nama, $lokasi, $mulai, $selesai, $ket);
}
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;