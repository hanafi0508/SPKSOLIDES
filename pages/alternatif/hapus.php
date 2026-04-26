<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];
$id_proyek = $_GET['proyek'];

mysqli_query($conn, "DELETE FROM alternatif WHERE id_alternatif='$id'");

header("Location: index.php?proyek=$id_proyek");
exit;