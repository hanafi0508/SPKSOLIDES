<?php

function rankingRepoGetAlternatifProyek(mysqli $conn, int $idProyek): array
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
        $alternatif[(int) $row['id_alternatif']] = $row;
    }

    return $alternatif;
}

function rankingRepoGetBobotAhpProyek(mysqli $conn, int $idProyek): array
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
        $bobot[(int) $row['id_kriteria']] = $row;
    }

    return $bobot;
}

function rankingRepoGetPenilaianProyek(mysqli $conn, int $idProyek): array
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
        $penilaian[(int) $row['id_alternatif']][(int) $row['id_kriteria']] = (float) $row['nilai'];
    }

    return $penilaian;
}

function rankingRepoReplaceRankingAhp(mysqli $conn, int $idProyek, array $ranking): void
{
    mysqli_begin_transaction($conn);

    try {
        $metode = 'AHP';
        $stmtDelete = mysqli_prepare($conn, "DELETE FROM hasil_perhitungan WHERE id_proyek = ? AND metode = ?");
        mysqli_stmt_bind_param($stmtDelete, "is", $idProyek, $metode);
        mysqli_stmt_execute($stmtDelete);

        $stmtInsert = mysqli_prepare($conn, "
            INSERT INTO hasil_perhitungan (id_proyek, id_alternatif, metode, nilai, ranking)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($ranking as $idAlternatif => $data) {
            $nilai = round((float) $data['nilai'], 6);
            $rank = (int) $data['ranking'];
            mysqli_stmt_bind_param($stmtInsert, "iisdi", $idProyek, $idAlternatif, $metode, $nilai, $rank);
            mysqli_stmt_execute($stmtInsert);
        }

        mysqli_commit($conn);
    } catch (Throwable $th) {
        mysqli_rollback($conn);
        throw $th;
    }
}

function rankingRepoGetRankingAhpTersimpan(mysqli $conn, int $idProyek): array
{
    $ranking = [];
    $metode = 'AHP';
    $stmt = mysqli_prepare($conn, "
        SELECT h.*, s.nama_supplier, s.tipe_supplier, s.jenis_material
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

function rankingRepoGetLaporanProyek(mysqli $conn, int $idProyek): ?array
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM proyek WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $proyek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $proyek ?: null;
}

function rankingRepoGetSupplierLaporan(mysqli $conn, int $idProyek): array
{
    $supplier = [];
    $stmt = mysqli_prepare($conn, "
        SELECT a.id_alternatif, s.nama_supplier, s.tipe_supplier, s.alamat, s.no_telepon, s.email, s.jenis_material, s.status
        FROM alternatif a
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE a.id_proyek = ?
        ORDER BY s.nama_supplier ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $supplier[] = $row;
    }

    return $supplier;
}

function rankingRepoGetKriteria(mysqli $conn): array
{
    $kriteria = [];
    $result = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

    while ($row = mysqli_fetch_assoc($result)) {
        $kriteria[] = $row;
    }

    return $kriteria;
}

function rankingRepoGetBobotAhpLaporan(mysqli $conn, int $idProyek): array
{
    $bobot = [];
    $stmt = mysqli_prepare($conn, "
        SELECT b.*, k.kode_kriteria, k.nama_kriteria, k.jenis_kriteria
        FROM bobot_ahp b
        JOIN kriteria k ON b.id_kriteria = k.id_kriteria
        WHERE b.id_proyek = ?
        ORDER BY k.id_kriteria ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $idProyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $bobot[] = $row;
    }

    return $bobot;
}

function rankingRepoGetProjectWorkflowCounts(mysqli $conn, int $idProyek): array
{
    $status = [
        'alternatif' => 0,
        'kriteria' => 0,
        'penilaian_terisi' => 0,
        'penilaian_harus' => 0,
        'ahp_total' => 0,
        'ahp_konsisten' => false,
    ];

    $stmtAlternatif = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM alternatif WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtAlternatif, "i", $idProyek);
    mysqli_stmt_execute($stmtAlternatif);
    $status['alternatif'] = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAlternatif))['total'] ?? 0);

    $queryKriteria = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kriteria");
    $status['kriteria'] = (int) (mysqli_fetch_assoc($queryKriteria)['total'] ?? 0);
    $status['penilaian_harus'] = $status['alternatif'] * $status['kriteria'];

    $stmtPenilaian = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM penilaian_supplier WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtPenilaian, "i", $idProyek);
    mysqli_stmt_execute($stmtPenilaian);
    $status['penilaian_terisi'] = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPenilaian))['total'] ?? 0);

    $stmtAhp = mysqli_prepare($conn, "
        SELECT COUNT(*) AS total, MAX(status_konsistensi = 'konsisten') AS konsisten
        FROM bobot_ahp
        WHERE id_proyek = ?
    ");
    mysqli_stmt_bind_param($stmtAhp, "i", $idProyek);
    mysqli_stmt_execute($stmtAhp);
    $ahpRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAhp));
    $status['ahp_total'] = (int) ($ahpRow['total'] ?? 0);
    $status['ahp_konsisten'] = (int) ($ahpRow['konsisten'] ?? 0) === 1;

    return $status;
}
