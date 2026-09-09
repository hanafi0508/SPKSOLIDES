<?php

require_once __DIR__ . '/../config/config.php';

function check_login() {
    if (!isset($_SESSION['id_user'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function hash_user_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_user_password($inputPassword, $storedPassword) {
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    // Backward compatibility untuk password plain text lama
    return hash_equals((string) $storedPassword, (string) $inputPassword);
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function alertRedirect($message, $url, $type = 'success') {
    set_flash($message, $type);
    header('Location: ' . $url);
    exit;
}

function verify_csrf() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return true;
    }

    $sent = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (empty($sent) || empty($stored) || !hash_equals($stored, $sent)) {
        http_response_code(403);
        die('Token CSRF tidak valid.');
    }

    return true;
}

function login_attempt_limit(): int
{
    return 5;
}

function login_attempt_window(): int
{
    return 300;
}

function login_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '0.0.0.0';
    }

    return $ip;
}

function login_is_blocked(mysqli $conn, string $username, string $ip): array
{
    // Lewati proteksi brutal kalau tabel login_attempts belum ada / DB error,
    // supaya halaman login tetap berfungsi (tidak fatal 500).
    try {
        mysqli_query($conn, 'DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');

        $limit = login_attempt_limit();
        $window = login_attempt_window();

        $stmt = mysqli_prepare(
            $conn,
            'SELECT COUNT(*) AS cnt,
                    IFNULL(TIMESTAMPDIFF(SECOND, MIN(attempted_at), NOW()), 0) AS elapsed
             FROM login_attempts
             WHERE attempted_at >= (NOW() - INTERVAL ? SECOND)
               AND (username = ? OR ip_address = ?)'
        );
        mysqli_stmt_bind_param($stmt, 'iss', $window, $username, $ip);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $count = (int) $row['cnt'];
    } catch (mysqli_sql_exception $e) {
        error_log('login_is_blocked gagal: ' . $e->getMessage());
        return ['blocked' => false, 'remaining' => 0, 'count' => 0];
    }

    if ($count < $limit) {
        return ['blocked' => false, 'remaining' => 0, 'count' => $count];
    }

    $remaining = max(0, $window - (int) $row['elapsed']);

    return ['blocked' => true, 'remaining' => $remaining, 'count' => $count];
}

function login_register_failure(mysqli $conn, string $username, string $ip): void
{
    try {
        $stmt = mysqli_prepare($conn, 'INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $username, $ip);
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('login_register_failure gagal: ' . $e->getMessage());
    }
}

function login_register_success(mysqli $conn, string $username): void
{
    try {
        $stmt = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('login_register_success gagal: ' . $e->getMessage());
    }
}
