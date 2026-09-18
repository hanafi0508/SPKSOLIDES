<?php

require_once __DIR__ . '/../config/config.php';

function check_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
}

function check_admin() {
    if ($_SESSION['level'] !== 'admin') {
        header("HTTP/1.1 403 Forbidden");
        echo "Akses ditolak";
        exit;
    }
}

function hash_user_password($password) {
    return password_hash((string) $password, PASSWORD_DEFAULT);
}

function verify_user_password($inputPassword, $storedPassword) {
    $inputPassword = (string) $inputPassword;
    $storedPassword = (string) $storedPassword;

    if (password_needs_upgrade_from_legacy($storedPassword)) {
        return hash_equals($storedPassword, $inputPassword);
    }

    return password_verify($inputPassword, $storedPassword);
}

function password_needs_upgrade_from_legacy($storedPassword) {
    $info = password_get_info((string) $storedPassword);

    return ($info['algo'] ?? 0) === 0;
}
