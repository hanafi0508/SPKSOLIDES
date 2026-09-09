<?php
require_once '../../functions/init.php';
require_auth('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
db_exec($conn, 'DELETE FROM supplier WHERE id_supplier = ?', 'i', [$id]);

alertRedirect('Supplier berhasil dihapus!', 'index.php');
exit;