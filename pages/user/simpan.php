<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

function redirError(string $message): void
{
    header("Location: index.php?error=" . urlencode($message));
    exit;
}

$id       = (int) ($_POST['id'] ?? 0);
$nama     = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$level    = $_POST['level'] ?? '';

if ($nama === '') {
    redirError("Nama wajib diisi");
}
if ($username === '') {
    redirError("Username wajib diisi");
}
if (!in_array($level, ['admin', 'pimpinan'], true)) {
    redirError("Level user tidak valid");
}
if ($id <= 0 && $password === '') {
    redirError("Password wajib diisi");
}

$stmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE username = ? AND id_user != ?");
mysqli_stmt_bind_param($stmt, "si", $username, $id);
mysqli_stmt_execute($stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
    redirError("Username sudah digunakan");
}

if ($id > 0) {
    if ($password !== '') {
        $passwordHash = hash_user_password($password);
        $stmt = mysqli_prepare($conn, "
            UPDATE users SET nama_user = ?, username = ?, password = ?, level = ? WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmt, "ssssi", $nama, $username, $passwordHash, $level, $id);
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE users SET nama_user = ?, username = ?, level = ? WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $username, $level, $id);
    }
} else {
    $passwordHash = hash_user_password($password);
    $stmt = mysqli_prepare($conn, "
        INSERT INTO users (nama_user, username, password, level) VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $passwordHash, $level);
}
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;