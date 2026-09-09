<?php
require_once '../../functions/init.php';
require_auth('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
db_exec($conn, 'DELETE FROM kriteria WHERE id_kriteria = ?', 'i', [$id]);

alertRedirect('Kriteria berhasil dihapus!', 'index.php');
exit;