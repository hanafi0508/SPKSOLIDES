<?php

function getAlternatifProyek(mysqli $conn, $idProyek)
{
    $alternatif = [];
    $stmt = mysqli_prepare($conn, "
        SELECT a.id_alternatif, s.nama_supplier
        FROM alternatif a
        JOIN supplier s ON s.id_supplier = a.id_supplier
        WHERE a.id_proyek = ?
        ORDER BY s.nama_supplier ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $alternatif[$row['id_alternatif']] = $row;
    }

    return $alternatif;
}

function getBobotAhpProyek(mysqli $conn, $idProyek)
{
    $bobot = [];
    $stmt = mysqli_prepare($conn, "
        SELECT b.id_kriteria, b.bobot, b.status_konsistensi, k.nama_kriteria, k.jenis_kriteria
        FROM bobot_ahp b
        JOIN kriteria k ON k.id_kriteria = b.id_kriteria
        WHERE b.id_proyek = ?
        ORDER BY k.id_kriteria ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $bobot[$row['id_kriteria']] = $row;
    }

    return $bobot;
}

function getBobotFahpProyek(mysqli $conn, $idProyek)
{
    $bobot = [];
    $stmt = mysqli_prepare($conn, "
        SELECT b.id_kriteria, b.bobot, k.nama_kriteria, k.jenis_kriteria
        FROM bobot_fahp b
        JOIN kriteria k ON k.id_kriteria = b.id_kriteria
        WHERE b.id_proyek = ?
        ORDER BY k.id_kriteria ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $bobot[$row['id_kriteria']] = $row;
    }

    return $bobot;
}

function getPenilaianProyek(mysqli $conn, $idProyek)
{
    $penilaian = [];
    $stmt = mysqli_prepare($conn, "
        SELECT id_alternatif, id_kriteria, nilai
        FROM penilaian_supplier
        WHERE id_proyek = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $penilaian[$row['id_alternatif']][$row['id_kriteria']] = (float) $row['nilai'];
    }

    return $penilaian;
}

function hitungRankingAhp(mysqli $conn, $idProyek)
{
    $alternatif = getAlternatifProyek($conn, $idProyek);
    $bobot = getBobotAhpProyek($conn, $idProyek);
    $penilaian = getPenilaianProyek($conn, $idProyek);

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

    return hitungRankingDenganBobot(
        $alternatif,
        $bobot,
        $penilaian,
        'Bobot AHP belum dihitung.'
    );
}

function hitungRankingDenganBobot(array $alternatif, array $bobot, array $penilaian, $errorMessage)
{
    if (count($alternatif) === 0) {
        throw new RuntimeException('Data alternatif untuk proyek ini belum tersedia.');
    }

    if (count($bobot) === 0) {
        throw new RuntimeException($errorMessage);
    }

    $normalisasi = [];
    $nilaiAkhir = [];
    $detail = [];

    foreach ($bobot as $idKriteria => $dataBobot) {
        $nilaiKriteria = [];

        foreach ($alternatif as $idAlternatif => $dataAlternatif) {
            if (!isset($penilaian[$idAlternatif][$idKriteria])) {
                throw new RuntimeException(
                    'Penilaian supplier belum lengkap untuk semua alternatif dan kriteria.'
                );
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
            'ranking' => $peringkat++,
            'nilai' => $nilai,
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

function hitungRankingFahp(mysqli $conn, $idProyek)
{
    $alternatif = getAlternatifProyek($conn, $idProyek);
    $bobot = getBobotFahpProyek($conn, $idProyek);
    $penilaian = getPenilaianProyek($conn, $idProyek);

    return hitungRankingDenganBobot(
        $alternatif,
        $bobot,
        $penilaian,
        'Bobot F-AHP belum dihitung.'
    );
}

function simpanRankingMetode(mysqli $conn, $idProyek, array $ranking, $metode)
{
    mysqli_begin_transaction($conn);

    try {
        $stmtDelete = mysqli_prepare($conn, "DELETE FROM hasil_perhitungan WHERE id_proyek = ? AND metode = ?");
        mysqli_stmt_bind_param($stmtDelete, "is", $idProyek, $metode);
        mysqli_stmt_execute($stmtDelete);

        $stmtInsert = mysqli_prepare($conn, "
            INSERT INTO hasil_perhitungan (id_proyek, id_alternatif, metode, nilai, ranking)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($ranking as $idAlternatif => $data) {
            $nilai = round($data['nilai'], 6);
            $rank = $data['ranking'];
            mysqli_stmt_bind_param($stmtInsert, "iisdi", $idProyek, $idAlternatif, $metode, $nilai, $rank);
            mysqli_stmt_execute($stmtInsert);
        }

        mysqli_commit($conn);
    } catch (Throwable $th) {
        mysqli_rollback($conn);
        throw $th;
    }
}

function simpanRankingAhp(mysqli $conn, $idProyek, array $ranking)
{
    simpanRankingMetode($conn, $idProyek, $ranking, 'AHP');
}

function simpanRankingFahp(mysqli $conn, $idProyek, array $ranking)
{
    simpanRankingMetode($conn, $idProyek, $ranking, 'F-AHP');
}

function getRankingTersimpan(mysqli $conn, $idProyek, $metode)
{
    $ranking = [];
    $stmt = mysqli_prepare($conn, "
        SELECT h.*, s.nama_supplier, s.jenis_material
        FROM hasil_perhitungan h
        JOIN alternatif a ON h.id_alternatif = a.id_alternatif
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE h.id_proyek = ? AND h.metode = ?
        ORDER BY h.ranking ASC
    ");
    mysqli_stmt_bind_param($stmt, "is", $idProyek, $metode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $ranking[] = $row;
    }

    return $ranking;
}

function getLaporanData(mysqli $conn, $idProyek)
{
    $data = [
        'proyek' => null,
        'supplier' => [],
        'kriteria' => [],
        'bobot_ahp' => [],
        'bobot_fahp' => [],
        'ranking_ahp' => [],
        'ranking_fahp' => [],
    ];

    $stmt = mysqli_prepare($conn, "SELECT * FROM proyek WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $data['proyek'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$data['proyek']) {
        return $data;
    }

    $stmtSupplier = mysqli_prepare($conn, "
        SELECT a.id_alternatif, s.nama_supplier, s.alamat, s.no_telepon, s.email, s.jenis_material, s.status
        FROM alternatif a
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE a.id_proyek = ?
        ORDER BY s.nama_supplier ASC
    ");
    mysqli_stmt_bind_param($stmtSupplier, "i", $idProyek);
    mysqli_stmt_execute($stmtSupplier);
    $resultSupplier = mysqli_stmt_get_result($stmtSupplier);
    while ($row = mysqli_fetch_assoc($resultSupplier)) {
        $data['supplier'][] = $row;
    }

    $queryKriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
    while ($row = mysqli_fetch_assoc($queryKriteria)) {
        $data['kriteria'][] = $row;
    }

    $stmtAhp = mysqli_prepare($conn, "
        SELECT b.*, k.kode_kriteria, k.nama_kriteria, k.jenis_kriteria
        FROM bobot_ahp b
        JOIN kriteria k ON b.id_kriteria = k.id_kriteria
        WHERE b.id_proyek = ?
        ORDER BY k.id_kriteria ASC
    ");
    mysqli_stmt_bind_param($stmtAhp, "i", $idProyek);
    mysqli_stmt_execute($stmtAhp);
    $resultAhp = mysqli_stmt_get_result($stmtAhp);
    while ($row = mysqli_fetch_assoc($resultAhp)) {
        $data['bobot_ahp'][] = $row;
    }

    $stmtFahp = mysqli_prepare($conn, "
        SELECT b.*, k.kode_kriteria, k.nama_kriteria, k.jenis_kriteria
        FROM bobot_fahp b
        JOIN kriteria k ON b.id_kriteria = k.id_kriteria
        WHERE b.id_proyek = ?
        ORDER BY k.id_kriteria ASC
    ");
    mysqli_stmt_bind_param($stmtFahp, "i", $idProyek);
    mysqli_stmt_execute($stmtFahp);
    $resultFahp = mysqli_stmt_get_result($stmtFahp);
    while ($row = mysqli_fetch_assoc($resultFahp)) {
        $data['bobot_fahp'][] = $row;
    }

    try {
        $hasilAhp = hitungRankingAhp($conn, $idProyek);
        simpanRankingAhp($conn, $idProyek, $hasilAhp['ranking']);
    } catch (Throwable $th) {
    }

    try {
        $hasilFahp = hitungRankingFahp($conn, $idProyek);
        simpanRankingFahp($conn, $idProyek, $hasilFahp['ranking']);
    } catch (Throwable $th) {
    }

    $data['ranking_ahp'] = getRankingTersimpan($conn, $idProyek, 'AHP');
    $data['ranking_fahp'] = getRankingTersimpan($conn, $idProyek, 'F-AHP');

    return $data;
}

function buildPerbandinganRanking(array $rankingAhp, array $rankingFahp)
{
    $mapAhp = [];
    foreach ($rankingAhp as $row) {
        $mapAhp[$row['id_alternatif']] = $row;
    }

    $mapFahp = [];
    foreach ($rankingFahp as $row) {
        $mapFahp[$row['id_alternatif']] = $row;
    }

    $perbandingan = [];
    foreach ($mapAhp as $idAlternatif => $ahp) {
        $fahp = $mapFahp[$idAlternatif] ?? null;
        $rankingAhpValue = (int) $ahp['ranking'];
        $rankingFahpValue = $fahp ? (int) $fahp['ranking'] : null;
        $selisih = $fahp ? abs($rankingAhpValue - $rankingFahpValue) : null;
        $status = ($fahp && $rankingAhpValue === $rankingFahpValue) ? 'Tetap' : 'Berubah';

        $perbandingan[] = [
            'id_alternatif' => $idAlternatif,
            'nama_supplier' => $ahp['nama_supplier'],
            'ranking_ahp' => $rankingAhpValue,
            'ranking_fahp' => $rankingFahpValue,
            'selisih' => $selisih,
            'status' => $status,
        ];
    }

    return $perbandingan;
}

function buildKesimpulanPerbandingan(array $rankingAhp, array $rankingFahp)
{
    $rekomendasiAhp = $rankingAhp[0] ?? null;
    $rekomendasiFahp = $rankingFahp[0] ?? null;

    if (!$rekomendasiAhp || !$rekomendasiFahp) {
        return 'Perbandingan metode belum lengkap karena ranking AHP atau F-AHP belum tersedia.';
    }

    if ($rekomendasiAhp['id_alternatif'] === $rekomendasiFahp['id_alternatif']) {
        return 'Metode AHP dan F-AHP menghasilkan supplier rekomendasi yang sama pada peringkat pertama.';
    }

    return 'Metode AHP dan F-AHP menghasilkan supplier peringkat pertama yang berbeda sehingga perlu pertimbangan manajerial tambahan.';
}
