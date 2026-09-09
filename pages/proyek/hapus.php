<?php
require_once '../../functions/init.php';
require_auth('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
db_exec($conn, 'DELETE FROM proyek WHERE id_proyek = ?', 'i', [$id]);

alertRedirect('Proyek berhasil dihapus!', 'index.php');
exit;