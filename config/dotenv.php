<?php

/**
 * Loader minimal .env (tanpa dependensi).
 *
 * Membaca file .env di root project lalu memasukkannya ke $_ENV dan
 * lingkungan proses (putenv) sehingga getenv('KEY') ikut terbaca.
 * Nilai OS/server yang sudah diset TIDAK ditimpa (file .env menang hanya
 * bila env belum tersedia). Komentar (# / ;) dan baris kosong diabaikan.
 */

function load_dotenv(?string $path = null): void
{
    $path = $path ?: dirname(__DIR__) . '/.env';

    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        if ($key === '') {
            continue;
        }

        // Lepas tanda kutip pembungkus (opsional).
        if (strlen($val) >= 2) {
            $first = $val[0];
            $last  = $val[strlen($val) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $val = substr($val, 1, -1);
            }
        }

        // Jangan timpa env yang sudah diset di environment OS/server.
        if (getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $val);
        $_ENV[$key]   = $val;
        $_SERVER[$key] = $val;
    }
}

load_dotenv();
