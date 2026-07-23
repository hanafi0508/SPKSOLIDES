<?php

require_once __DIR__ . '/ranking_repository.php';

function hitungRankingAhp(mysqli $conn, int $idProyek): array
{
    $alternatif = rankingRepoGetAlternatifProyek($conn, $idProyek);
    $bobot = rankingRepoGetBobotAhpProyek($conn, $idProyek);
    $penilaian = rankingRepoGetPenilaianProyek($conn, $idProyek);

    if (count($alternatif) === 0) {
        throw new RuntimeException('Data alternatif untuk proyek ini belum tersedia.');
    }

    if (count($bobot) === 0) {
        throw new RuntimeException('Bobot AHP belum dihitung.');
    }

    $status = reset($bobot)['status_konsistensi'] ?? 'tidak_konsisten';
    if ($status !== 'konsisten') {
        throw new RuntimeException('Bobot AHP belum konsisten.');
    }

    return hitungRankingDenganBobot($alternatif, $bobot, $penilaian);
}

function hitungRankingDenganBobot(array $alternatif, array $bobot, array $penilaian): array
{
    $normalisasi = [];
    $nilaiAkhir = [];
    $detail = [];

    foreach ($bobot as $idKriteria => $dataBobot) {
        $nilaiKriteria = [];

        foreach ($alternatif as $idAlternatif => $dataAlternatif) {
            if (!isset($penilaian[$idAlternatif][$idKriteria])) {
                throw new RuntimeException('Penilaian supplier belum lengkap untuk semua alternatif dan kriteria.');
            }

            $nilaiKriteria[$idAlternatif] = $penilaian[$idAlternatif][$idKriteria];
        }

        $maksimum = max($nilaiKriteria);
        $minimum = min($nilaiKriteria);

        foreach ($nilaiKriteria as $idAlternatif => $nilai) {
            if ($dataBobot['jenis_kriteria'] === 'benefit') {
                $normal = $maksimum > 0 ? $nilai / $maksimum : 0;
            } else {
                $normal = $nilai > 0 ? $minimum / $nilai : 0;
            }

            $skor = $normal * (float) $dataBobot['bobot'];

            $normalisasi[$idAlternatif][$idKriteria] = $normal;
            $nilaiAkhir[$idAlternatif] = ($nilaiAkhir[$idAlternatif] ?? 0) + $skor;
            $detail[$idAlternatif][$idKriteria] = [
                'nilai_asli' => $nilai,
                'normalisasi' => $normal,
                'bobot' => (float) $dataBobot['bobot'],
                'skor' => $skor,
            ];
        }
    }

    arsort($nilaiAkhir);

    $ranking = [];
    $peringkat = 1;
    foreach ($nilaiAkhir as $idAlternatif => $nilai) {
        $ranking[$idAlternatif] = [
            'id_alternatif' => $idAlternatif,
            'ranking' => $peringkat++,
            'nilai' => $nilai,
            'nama_supplier' => $alternatif[$idAlternatif]['nama_supplier'],
            'supplier' => $alternatif[$idAlternatif]['nama_supplier'],
        ];
    }

    return [
        'alternatif' => $alternatif,
        'bobot' => $bobot,
        'normalisasi' => $normalisasi,
        'detail' => $detail,
        'ranking' => $ranking,
    ];
}

function simpanRankingAhp(mysqli $conn, int $idProyek, array $ranking): void
{
    rankingRepoReplaceRankingAhp($conn, $idProyek, $ranking);
}

function getRankingTersimpan(mysqli $conn, int $idProyek, string $metode = 'AHP'): array
{
    if ($metode !== 'AHP') {
        return [];
    }

    return rankingRepoGetRankingAhpTersimpan($conn, $idProyek);
}

function prosesRankingAhp(mysqli $conn, int $idProyek): array
{
    $hasil = hitungRankingAhp($conn, $idProyek);
    simpanRankingAhp($conn, $idProyek, $hasil['ranking']);

    return $hasil;
}

function getLaporanData(mysqli $conn, int $idProyek): array
{
    $data = [
        'proyek' => rankingRepoGetLaporanProyek($conn, $idProyek),
        'supplier' => [],
        'kriteria' => [],
        'bobot_ahp' => [],
        'ranking_ahp' => [],
    ];

    if (!$data['proyek']) {
        return $data;
    }

    $data['supplier'] = rankingRepoGetSupplierLaporan($conn, $idProyek);
    $data['kriteria'] = rankingRepoGetKriteria($conn);
    $data['bobot_ahp'] = rankingRepoGetBobotAhpLaporan($conn, $idProyek);

    try {
        prosesRankingAhp($conn, $idProyek);
    } catch (Throwable $th) {
    }

    $data['ranking_ahp'] = rankingRepoGetRankingAhpTersimpan($conn, $idProyek);

    return $data;
}

function getProjectWorkflowStatus(mysqli $conn, int $idProyek): array
{
    return rankingRepoGetProjectWorkflowCounts($conn, $idProyek);
}

function getProjectWorkflowIssues(array $status): array
{
    $issues = [];

    if ($status['alternatif'] === 0) {
        $issues[] = 'Belum ada supplier yang dimasukkan ke menu Perhitungan.';
    }

    if ($status['kriteria'] === 0) {
        $issues[] = 'Data kriteria belum tersedia.';
    }

    if ($status['penilaian_harus'] > 0 && $status['penilaian_terisi'] < $status['penilaian_harus']) {
        $issues[] = 'Penilaian supplier belum lengkap untuk semua kombinasi supplier dan kriteria.';
    }

    if ($status['ahp_total'] === 0) {
        $issues[] = 'Bobot AHP belum dihitung.';
    } elseif (!$status['ahp_konsisten']) {
        $issues[] = 'Bobot AHP sudah ada tetapi belum konsisten.';
    }

    return $issues;
}
