<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$id_proyek = isset($_GET['proyek']) ? (int) $_GET['proyek'] : 0;
$stmt = mysqli_prepare($conn, "DELETE FROM alternatif WHERE id_alternatif = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: index.php?proyek=$id_proyek");
exit;
