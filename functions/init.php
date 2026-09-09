<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_function.php';
require_once __DIR__ . '/../layouts/layout.php';

function require_auth($role = null) {
    check_login();

    if ($role && ($_SESSION['level'] ?? '') !== $role) {
        echo 'Akses ditolak';
        exit;
    }
}

function db_all($conn, $sql, $types = '', $params = []): array {
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function db_one($conn, $sql, $types = '', $params = []): ?array {
    $rows = db_all($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

function db_exec($conn, $sql, $types = '', $params = []): void {
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
}
