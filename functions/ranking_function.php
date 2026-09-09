<?php

require_once __DIR__ . '/ranking_repository.php';
require_once __DIR__ . '/ahp_function.php';

function hitungSkorSupplier(array $supplier, array $bobotKriteria, array $skorPerKriteria): array
{
    if (count($supplier) < 2) {
        throw new RuntimeException('Minimal 2 supplier untuk perhitungan.');
    }

    if (count($bobotKriteria) === 0) {
        throw new RuntimeException('Bobot kriteria belum dihitung.');
    }

    foreach ($bobotKriteria as $dataBobot) {
        if (($dataBobot['status_konsistensi'] ?? '') !== 'konsisten') {
            throw new RuntimeException('Bobot kriteria belum konsisten.');
        }
    }

    $detail = [];
    $nilaiAkhir = [];

    foreach ($bobotKriteria as $idKriteria => $dataBobot) {
        $bobot = (float) $dataBobot['bobot'];

        foreach ($supplier as $idSupplier => $dataSupplier) {
            $skor = (int) ($skorPerKriteria[$idKriteria][$idSupplier] ?? 0);

            if ($skor < 1 || $skor > 9) {
                throw new RuntimeException('Skor supplier belum lengkap (1-9) pada kriteria "' . $dataBobot['nama_kriteria'] . '".');
            }

            $skorBobot = $skor * $bobot;
            $detail[$idSupplier][$idKriteria] = [
                'skor' => $skor,
                'bobot' => $bobot,
                'skor_bobot' => $skorBobot,
            ];
            $nilaiAkhir[$idSupplier] = ($nilaiAkhir[$idSupplier] ?? 0) + $skorBobot;
        }
    }

    arsort($nilaiAkhir);

    $ranking = [];
    $peringkat = 1;
    foreach ($nilaiAkhir as $idSupplier => $nilai) {
        $ranking[] = [
            'id_supplier' => $idSupplier,
            'ranking' => $peringkat++,
            'nilai' => $nilai,
            'nama_supplier' => $supplier[$idSupplier]['nama_supplier'],
        ];
    }

    return [
        'detail' => $detail,
        'ranking' => $ranking,
    ];
}

function getLaporanData(mysqli $conn): array
{
    return [
        'supplier' => array_values(rankingRepoGetSupplierAktif($conn)),
        'kriteria' => rankingRepoGetKriteria($conn),
        'bobot_ahp' => rankingRepoGetBobotAhpLaporan($conn),
        'skor_supplier' => rankingRepoGetSkorSupplierLaporan($conn),
        'ranking_ahp' => rankingRepoGetRankingTersimpan($conn),
    ];
}


