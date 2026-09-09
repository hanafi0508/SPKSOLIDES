<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

if (!isset($_POST['submit'])) {
    header('Location: index.php');
    exit;
}

$id       = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nama     = trim($_POST['nama'] ?? '');
$telepon  = trim($_POST['telepon'] ?? '');
$email    = trim($_POST['email'] ?? '');
$material = trim($_POST['material'] ?? '');
$status   = in_array($_POST['status'] ?? '', ['aktif', 'tidak_aktif'], true) ? $_POST['status'] : 'aktif';

if ($nama === '' || $material === '') {
    alertRedirect('Nama supplier dan barang wajib diisi.', 'index.php', 'error');
}

if ($id > 0) {
    db_exec($conn, 'UPDATE supplier SET nama_supplier = ?, no_telepon = ?, email = ?, jenis_material = ?, status = ? WHERE id_supplier = ?',
        'sssssi', [$nama, $telepon, $email, $material, $status, $id]);
    alertRedirect('Supplier berhasil diperbarui!', 'index.php');
} else {
    db_exec($conn, 'INSERT INTO supplier (nama_supplier, no_telepon, email, jenis_material, status) VALUES (?, ?, ?, ?, ?)',
        'sssss', [$nama, $telepon, $email, $material, $status]);
    alertRedirect('Supplier berhasil ditambahkan!', 'index.php');
}