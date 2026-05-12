<?php

function getRI($n)
{
    $ri = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49
    ];

    return $ri[$n] ?? 1.49;
}

function buildAhpMatrix($kriteria, $perbandingan)
{
    $matrix = [];

    foreach ($kriteria as $row) {
        $id1 = $row['id_kriteria'];

        foreach ($kriteria as $col) {
            $id2 = $col['id_kriteria'];

            if ($id1 == $id2) {
                $matrix[$id1][$id2] = 1;
            } elseif (isset($perbandingan[$id1][$id2])) {
                $matrix[$id1][$id2] = (float) $perbandingan[$id1][$id2];
            } elseif (isset($perbandingan[$id2][$id1])) {
                $matrix[$id1][$id2] = 1 / (float) $perbandingan[$id2][$id1];
            } else {
                $matrix[$id1][$id2] = 1;
            }
        }
    }

    return $matrix;
}

function hitungTotalKolom($matrix, $kriteria)
{
    $totalKolom = [];

    foreach ($kriteria as $col) {
        $idCol = $col['id_kriteria'];
        $totalKolom[$idCol] = 0;

        foreach ($kriteria as $row) {
            $idRow = $row['id_kriteria'];
            $totalKolom[$idCol] += $matrix[$idRow][$idCol];
        }
    }

    return $totalKolom;
}

function hitungNormalisasi($matrix, $totalKolom, $kriteria)
{
    $normalisasi = [];

    foreach ($kriteria as $row) {
        $idRow = $row['id_kriteria'];

        foreach ($kriteria as $col) {
            $idCol = $col['id_kriteria'];
            $normalisasi[$idRow][$idCol] = $matrix[$idRow][$idCol] / $totalKolom[$idCol];
        }
    }

    return $normalisasi;
}

function hitungBobotPrioritas($normalisasi, $kriteria)
{
    $bobot = [];
    $n = count($kriteria);

    foreach ($kriteria as $row) {
        $idRow = $row['id_kriteria'];
        $jumlah = 0;

        foreach ($kriteria as $col) {
            $idCol = $col['id_kriteria'];
            $jumlah += $normalisasi[$idRow][$idCol];
        }

        $bobot[$idRow] = $jumlah / $n;
    }

    return $bobot;
}

function hitungWeightedSumVector($matrix, $bobot, $kriteria)
{
    $wsv = [];

    foreach ($kriteria as $row) {
        $idRow = $row['id_kriteria'];
        $jumlah = 0;

        foreach ($kriteria as $col) {
            $idCol = $col['id_kriteria'];
            $jumlah += $matrix[$idRow][$idCol] * $bobot[$idCol];
        }

        $wsv[$idRow] = $jumlah;
    }

    return $wsv;
}

function hitungConsistencyVector($wsv, $bobot, $kriteria)
{
    $cv = [];

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];

        if ($bobot[$id] == 0) {
            $cv[$id] = 0;
        } else {
            $cv[$id] = $wsv[$id] / $bobot[$id];
        }
    }

    return $cv;
}

function hitungLambdaMaksimum($cv, $kriteria)
{
    $jumlah = 0;
    $n = count($kriteria);

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];
        $jumlah += $cv[$id];
    }

    return $jumlah / $n;
}

function hitungAHP($kriteria, $perbandingan)
{
    $n = count($kriteria);

    $matrix = buildAhpMatrix($kriteria, $perbandingan);
    $totalKolom = hitungTotalKolom($matrix, $kriteria);
    $normalisasi = hitungNormalisasi($matrix, $totalKolom, $kriteria);
    $bobot = hitungBobotPrioritas($normalisasi, $kriteria);
    $wsv = hitungWeightedSumVector($matrix, $bobot, $kriteria);
    $cv = hitungConsistencyVector($wsv, $bobot, $kriteria);
    $lambdaMaks = hitungLambdaMaksimum($cv, $kriteria);

    if ($n <= 2) {
        $ci = 0;
        $cr = 0;
    } else {
        $ci = ($lambdaMaks - $n) / ($n - 1);
        $ri = getRI($n);
        $cr = ($ri == 0) ? 0 : $ci / $ri;
    }

    $status = ($cr <= 0.10) ? 'konsisten' : 'tidak_konsisten';

    return [
        'matrix' => $matrix,
        'total_kolom' => $totalKolom,
        'normalisasi' => $normalisasi,
        'bobot' => $bobot,
        'wsv' => $wsv,
        'cv' => $cv,
        'lambda_maks' => $lambdaMaks,
        'ci' => $ci,
        'cr' => $cr,
        'status_konsistensi' => $status
    ];
}
?>