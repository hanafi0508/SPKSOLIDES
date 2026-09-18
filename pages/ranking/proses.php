<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();
check_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_proyek = isset($_POST['id_proyek']) ? (int) $_POST['id_proyek'] : 0;

if ($id_proyek <= 0) {
    header("Location: index.php");
    exit;
}

try {
    prosesRankingAhp($conn, $id_proyek);
    header("Location: index.php?id_proyek=$id_proyek&status=sukses");
} catch (Throwable $th) {
    header("Location: index.php?id_proyek=$id_proyek&status=gagal");
}

exit;