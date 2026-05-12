<?php

function reciprocalFuzzy($fuzzy)
{
    return [
        'l' => 1 / $fuzzy['u'],
        'm' => 1 / $fuzzy['m'],
        'u' => 1 / $fuzzy['l']
    ];
}

function tambahFuzzy($a, $b)
{
    return [
        'l' => $a['l'] + $b['l'],
        'm' => $a['m'] + $b['m'],
        'u' => $a['u'] + $b['u']
    ];
}

function kaliFuzzy($a, $b)
{
    return [
        'l' => $a['l'] * $b['l'],
        'm' => $a['m'] * $b['m'],
        'u' => $a['u'] * $b['u']
    ];
}

function buildFahpMatrix($kriteria, $perbandingan)
{
    $matrix = [];

    foreach ($kriteria as $row) {
        $id1 = $row['id_kriteria'];

        foreach ($kriteria as $col) {
            $id2 = $col['id_kriteria'];

            if ($id1 == $id2) {
                $matrix[$id1][$id2] = [
                    'l' => 1,
                    'm' => 1,
                    'u' => 1
                ];
            } elseif (isset($perbandingan[$id1][$id2])) {
                $matrix[$id1][$id2] = $perbandingan[$id1][$id2];
            } elseif (isset($perbandingan[$id2][$id1])) {
                $matrix[$id1][$id2] = reciprocalFuzzy($perbandingan[$id2][$id1]);
            } else {
                $matrix[$id1][$id2] = [
                    'l' => 1,
                    'm' => 1,
                    'u' => 1
                ];
            }
        }
    }

    return $matrix;
}

function hitungRowSumFuzzy($matrix, $kriteria)
{
    $rowSum = [];

    foreach ($kriteria as $row) {
        $idRow = $row['id_kriteria'];

        $jumlah = [
            'l' => 0,
            'm' => 0,
            'u' => 0
        ];

        foreach ($kriteria as $col) {
            $idCol = $col['id_kriteria'];
            $jumlah = tambahFuzzy($jumlah, $matrix[$idRow][$idCol]);
        }

        $rowSum[$idRow] = $jumlah;
    }

    return $rowSum;
}

function hitungTotalFuzzy($rowSum, $kriteria)
{
    $total = [
        'l' => 0,
        'm' => 0,
        'u' => 0
    ];

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];
        $total = tambahFuzzy($total, $rowSum[$id]);
    }

    return $total;
}

function inversTotalFuzzy($totalFuzzy)
{
    return [
        'l' => 1 / $totalFuzzy['u'],
        'm' => 1 / $totalFuzzy['m'],
        'u' => 1 / $totalFuzzy['l']
    ];
}

function hitungSyntheticExtent($rowSum, $inversTotal, $kriteria)
{
    $synthetic = [];

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];
        $synthetic[$id] = kaliFuzzy($rowSum[$id], $inversTotal);
    }

    return $synthetic;
}

function derajatKemungkinan($m1, $m2)
{
    if ($m1['m'] >= $m2['m']) {
        return 1;
    }

    if ($m2['l'] >= $m1['u']) {
        return 0;
    }

    $pembilang = $m2['l'] - $m1['u'];
    $penyebut = ($m1['m'] - $m1['u']) - ($m2['m'] - $m2['l']);

    if ($penyebut == 0) {
        return 0;
    }

    $hasil = $pembilang / $penyebut;

    if ($hasil < 0) {
        return 0;
    }

    if ($hasil > 1) {
        return 1;
    }

    return $hasil;
}

function hitungDerajatKemungkinan($synthetic, $kriteria)
{
    $derajat = [];

    foreach ($kriteria as $row1) {
        $id1 = $row1['id_kriteria'];
        $derajat[$id1] = [];

        foreach ($kriteria as $row2) {
            $id2 = $row2['id_kriteria'];

            if ($id1 != $id2) {
                $derajat[$id1][$id2] = derajatKemungkinan($synthetic[$id1], $synthetic[$id2]);
            }
        }
    }

    return $derajat;
}

function hitungMinimumDerajat($derajat, $kriteria)
{
    $minimum = [];

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];

        if (!empty($derajat[$id])) {
            $minimum[$id] = min($derajat[$id]);
        } else {
            $minimum[$id] = 1;
        }
    }

    return $minimum;
}

function normalisasiBobotFahp($minimum, $kriteria)
{
    $total = 0;
    $bobot = [];

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];
        $total += $minimum[$id];
    }

    foreach ($kriteria as $row) {
        $id = $row['id_kriteria'];

        if ($total == 0) {
            $bobot[$id] = 1 / count($kriteria);
        } else {
            $bobot[$id] = $minimum[$id] / $total;
        }
    }

    return $bobot;
}

function hitungFAHP($kriteria, $perbandingan)
{
    $matrix = buildFahpMatrix($kriteria, $perbandingan);
    $rowSum = hitungRowSumFuzzy($matrix, $kriteria);
    $totalFuzzy = hitungTotalFuzzy($rowSum, $kriteria);
    $inversTotal = inversTotalFuzzy($totalFuzzy);
    $synthetic = hitungSyntheticExtent($rowSum, $inversTotal, $kriteria);
    $derajat = hitungDerajatKemungkinan($synthetic, $kriteria);
    $minimum = hitungMinimumDerajat($derajat, $kriteria);
    $bobot = normalisasiBobotFahp($minimum, $kriteria);

    return [
        'matrix' => $matrix,
        'row_sum' => $rowSum,
        'total_fuzzy' => $totalFuzzy,
        'invers_total' => $inversTotal,
        'synthetic' => $synthetic,
        'derajat' => $derajat,
        'minimum' => $minimum,
        'bobot' => $bobot
    ];
}
?>