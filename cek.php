<?php
// DIAGNOSA SEMENTARA — hapus file ini setelah masalah selesai.
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "PHP: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '?') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '?') . "\n\n";

echo "=== ENV DB (getenv) ===\n";
foreach (['APP_ENV', 'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'] as $k) {
    $v = getenv($k);
    echo "$k = " . ($v === false ? '(TIDAK tersedia)' : ($k === 'DB_PASS' ? '(terisi)' : $v)) . "\n";
}

echo "\n=== mysqli tersedia? ===\n";
echo extension_loaded('mysqli') ? "ya\n" : "TIDAK — instal ekstensi mysqli!\n";

echo "\n=== Uji koneksi DB ===\n";
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'spk_supplier';
echo "host=$DB_HOST user=$DB_USER db=$DB_NAME\n";
$c = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$c) {
    echo "KONEKSI GAGAL: " . mysqli_connect_error() . "\n";
    echo "\nSolusi: buat config/database.local.php dengan kredensial DB benar di server.\n";
} else {
    echo "Koneksi berhasil.\n";
    $t = @mysqli_query($c, "SHOW TABLES LIKE 'login_attempts'");
    echo "Tabel login_attempts: " . (mysqli_num_rows($t) ? "ADA\n" : "TIDAK ADA — jalankan database/migration_20260909_login_attempts.sql\n");
    mysqli_close($c);
}

echo "\n=== Error log lokasi umum (cPanel/shared) ===\n";
echo "error_log: " . (ini_get('error_log') ?: '(default per-hosting)') . "\n";
echo "Cek panel hosting -> " . (PHP_OS_FAMILY === 'Windows' ? 'Logs' : 'Error Log / Apache Error Log') . "\n";
