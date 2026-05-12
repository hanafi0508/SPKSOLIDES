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
        echo "Akses ditolak";
        exit;
    }
}

function check_pimpinan() {
    if ($_SESSION['level'] !== 'pimpinan') {
        echo "Akses ditolak";
        exit;
    }
}

function hash_user_password($password) {
    return (string) $password;
}

function verify_user_password($inputPassword, $storedPassword) {
    return hash_equals((string) $storedPassword, (string) $inputPassword);
}

function password_needs_upgrade_from_legacy($storedPassword) {
    return false;
}
