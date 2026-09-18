<?php
require_once '../config/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/auth_function.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: " . BASE_URL . "/auth/login.php?error=1");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && verify_user_password($password, $user['password'])) {
    if (password_needs_upgrade_from_legacy($user['password'])) {
        $newHash = hash_user_password($password);
        $stmtUpgrade = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmtUpgrade, "si", $newHash, $user['id_user']);
        mysqli_stmt_execute($stmtUpgrade);
    }

    $_SESSION['id_user']   = $user['id_user'];
    $_SESSION['nama_user'] = $user['nama_user'];
    $_SESSION['level']     = $user['level'];

    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;

} else {
    header("Location: " . BASE_URL . "/auth/login.php?error=1");
    exit;
}
