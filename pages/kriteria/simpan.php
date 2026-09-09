<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$kode = strtoupper(trim($_POST['kode'] ?? ''));
$nama = trim($_POST['nama'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$url = 'index.php';

if (empty($kode) || empty($nama)) {
    alertRedirect('Kode dan nama wajib diisi!', $url, 'error');
}

if ($id > 0) {
    $dupe = db_one($conn, 'SELECT id_kriteria FROM kriteria WHERE kode_kriteria = ? AND id_kriteria <> ?', 'si', [$kode, $id]);
    if ($dupe) {
        alertRedirect('Kode sudah digunakan!', $url, 'error');
    }
    db_exec($conn, 'UPDATE kriteria SET kode_kriteria = ?, nama_kriteria = ?, deskripsi = ? WHERE id_kriteria = ?', 'sssi', [$kode, $nama, $deskripsi, $id]);
    alertRedirect('Kriteria berhasil diperbarui!', $url);
} else {
    $dupe = db_one($conn, 'SELECT id_kriteria FROM kriteria WHERE kode_kriteria = ?', 's', [$kode]);
    if ($dupe) {
        alertRedirect('Kode sudah digunakan!', $url, 'error');
    }
    db_exec($conn, 'INSERT INTO kriteria (kode_kriteria, nama_kriteria, deskripsi) VALUES (?, ?, ?)', 'sss', [$kode, $nama, $deskripsi]);
    alertRedirect('Kriteria berhasil ditambahkan!', $url);
}
