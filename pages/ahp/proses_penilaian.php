<?php
require_once '../../functions/init.php';
require_once '../../functions/ranking_function.php';
require_auth('admin');
verify_csrf();

$isXhr = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function respondJson(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $code < 400, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: input_alternatif.php');
    exit;
}

$skorPerKriteria = [];

foreach (($_POST['skor'] ?? []) as $idKriteria => $rows) {
    $idKriteria = (int) $idKriteria;

    foreach (($rows ?? []) as $idSupplier => $skor) {
        $idSupplier = (int) $idSupplier;
        $skor = (int) $skor;

        if ($idKriteria <= 0 || $idSupplier <= 0 || $skor < 1 || $skor > 9) {
            continue;
        }

        $skorPerKriteria[$idKriteria][$idSupplier] = $skor;
    }
}

try {
    $supplier = rankingRepoGetSupplierAktif($conn);
    $bobotKriteria = rankingRepoGetBobotAhp($conn);

    $skorTerisi = array_sum(array_map('count', $skorPerKriteria));
    $skorHarus = count($bobotKriteria) * count($supplier);

    if ($skorTerisi !== $skorHarus) {
        $pesan = 'Semua skor supplier (1-9) untuk setiap kriteria wajib diisi.';
        if ($isXhr) {
            respondJson(400, $pesan);
        }
        alertRedirect($pesan, 'input_alternatif.php', 'error');
    }

    $hasil = hitungSkorSupplier($supplier, $bobotKriteria, $skorPerKriteria);

    rankingRepoSimpanHasil($conn, $skorPerKriteria, $hasil['ranking']);

    $pesan = 'Penilaian supplier berhasil disimpan, ranking telah dihitung!';
    if ($isXhr) {
        respondJson(200, $pesan);
    }
    alertRedirect($pesan, 'input_alternatif.php');
} catch (Throwable $th) {
    if ($isXhr) {
        respondJson(400, $th->getMessage());
    }
    alertRedirect($th->getMessage(), 'input_alternatif.php', 'error');
}