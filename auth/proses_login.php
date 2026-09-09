<?php
require_once '../config/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/auth_function.php';

verify_csrf();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ' . BASE_URL . '/auth/login.php?error=1');
    exit;
}

$ip = login_client_ip();
$block = login_is_blocked($conn, $username, $ip);

if ($block['blocked']) {
    $_SESSION['last_login_user'] = $username;
    header('Location: ' . BASE_URL . '/auth/login.php?locked=1&wait=' . ceil($block['remaining'] / 60));
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT id_user, nama_user, password, level FROM users WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($user && verify_user_password($password, $user['password'])) {

    // Upgrade password plain text lama ke bcrypt
    if (!password_verify($password, $user['password'])) {
        $newHash = hash_user_password($password);
        $stmtUp = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id_user = ?');
        mysqli_stmt_bind_param($stmtUp, 'si', $newHash, $user['id_user']);
        mysqli_stmt_execute($stmtUp);
    }

    login_register_success($conn, $username);
    unset($_SESSION['last_login_user']);

    session_regenerate_id(true);

    $_SESSION['id_user']   = $user['id_user'];
    $_SESSION['nama_user'] = $user['nama_user'];
    $_SESSION['level']     = $user['level'];

    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

login_register_failure($conn, $username, $ip);
$_SESSION['last_login_user'] = $username;

$block = login_is_blocked($conn, $username, $ip);

if ($block['blocked']) {
    header('Location: ' . BASE_URL . '/auth/login.php?locked=1&wait=' . ceil($block['remaining'] / 60));
    exit;
}

header('Location: ' . BASE_URL . '/auth/login.php?error=1');
exit;
