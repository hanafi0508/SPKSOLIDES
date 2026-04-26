<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM proyek WHERE id_proyek='$id'");

header("Location: index.php");
exit;