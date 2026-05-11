<?php
require_once '../config/session.php';
require_once '../config/database.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: login.php?error=1");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && $password === $user['password']) {
    $_SESSION['id_user']   = $user['id_user'];
    $_SESSION['nama_user'] = $user['nama_user'];
    $_SESSION['level']     = $user['level'];

    header("Location: ../pages/dashboard.php");
    exit;

} else {
    header("Location: login.php?error=1");
    exit;
}
