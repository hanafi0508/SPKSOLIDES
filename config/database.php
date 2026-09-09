<?php

require_once __DIR__ . '/dotenv.php';

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'spk_supplier';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    error_log('Koneksi database gagal: ' . mysqli_connect_error());
    die('Koneksi database gagal. Periksa kembali konfigurasi .env (variabel DB_HOST, DB_USER, DB_PASS, DB_NAME).');
}

mysqli_set_charset($conn, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
