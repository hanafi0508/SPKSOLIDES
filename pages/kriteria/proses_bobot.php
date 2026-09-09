<?php
require_once '../../functions/init.php';
require_once '../../functions/ranking_function.php';
require_auth('admin');
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$inputPerbandingan = $_POST['perbandingan'] ?? [];

$kriteria = rankingRepoGetKriteria($conn);

if (count($kriteria) < 2) {
    alertRedirect('Data kriteria minimal harus 2 untuk menghitung bobot AHP!', 'index.php#bobot', 'error');
}

$perbandingan = [];
$jumlah = 0;

foreach ($inputPerbandingan as $id1 => $data) {
    $id1 = (int) $id1;

    foreach ($data as $id2 => $nilai) {
        $id2 = (int) $id2;
        $nilai = (float) $nilai;

        if ($id1 <= 0 || $id2 <= 0 || $nilai <= 0) {
            continue;
        }

        $perbandingan[$id1][$id2] = $nilai;
        $jumlah++;
    }
}

if (empty($perbandingan)) {
    alertRedirect('Data perbandingan belum diisi!', 'index.php#bobot', 'error');
}

$jumlahWajib = (count($kriteria) * (count($kriteria) - 1)) / 2;
if ($jumlah !== $jumlahWajib) {
    alertRedirect('Semua pasangan kriteria AHP wajib diisi.', 'index.php#bobot', 'error');
}

$hasil = hitungAHP($kriteria, $perbandingan);

mysqli_begin_transaction($conn);

try {
    db_exec($conn, 'DELETE FROM perbandingan_ahp');
    db_exec($conn, 'DELETE FROM bobot_ahp');

    foreach ($perbandingan as $id1 => $data) {
        foreach ($data as $id2 => $nilai) {
            db_exec($conn, 'INSERT INTO perbandingan_ahp (id_kriteria_1, id_kriteria_2, nilai) VALUES (?, ?, ?)', 'iid', [$id1, $id2, $nilai]);
        }
    }

    foreach ($kriteria as $k) {
        $id = (int) $k['id_kriteria'];
        db_exec($conn, 'INSERT INTO bobot_ahp (id_kriteria, bobot, lambda_value, ci, cr, status_konsistensi) VALUES (?, ?, ?, ?, ?, ?)',
            'idddds', [$id, round($hasil['bobot'][$id], 6), round($hasil['lambda_maks'], 6), round($hasil['ci'], 6), round($hasil['cr'], 6), $hasil['status_konsistensi']]);
    }

    mysqli_commit($conn);
    alertRedirect('Bobot kriteria AHP berhasil disimpan!', 'index.php#bobot');
} catch (Throwable $th) {
    mysqli_rollback($conn);
    alertRedirect('Terjadi kesalahan saat menghitung bobot.', 'index.php#bobot', 'error');
}
