<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

if (!isset($_POST['submit'])) {
    header('Location: index.php');
    exit;
}

$id         = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nama       = trim($_POST['nama'] ?? '');
$lokasi     = trim($_POST['lokasi'] ?? '');
$mulai      = trim($_POST['mulai'] ?? '');
$selesai    = trim($_POST['selesai'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($nama === '') {
    alertRedirect('Nama proyek wajib diisi.', 'index.php', 'error');
}

if ($id > 0) {
    db_exec($conn, 'UPDATE proyek SET nama_proyek = ?, lokasi = ?, tanggal_mulai = ?, tanggal_selesai = ?, keterangan = ? WHERE id_proyek = ?',
        'sssssi', [$nama, $lokasi, $mulai, $selesai, $keterangan, $id]);
    alertRedirect('Proyek berhasil diperbarui!', 'index.php');
} else {
    db_exec($conn, 'INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, keterangan) VALUES (?, ?, ?, ?, ?)',
        'sssss', [$nama, $lokasi, $mulai, $selesai, $keterangan]);
    alertRedirect('Proyek berhasil ditambahkan!', 'index.php');
}