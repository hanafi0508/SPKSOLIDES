<?php

function rankingRepoGetSupplierAktif(mysqli $conn): array
{
    $supplier = [];

    foreach (db_all($conn, "SELECT * FROM supplier WHERE status = 'aktif' ORDER BY nama_supplier ASC") as $row) {
        $supplier[(int) $row['id_supplier']] = $row;
    }

    return $supplier;
}

function rankingRepoGetKriteria(mysqli $conn): array
{
    return db_all($conn, 'SELECT * FROM kriteria ORDER BY id_kriteria ASC');
}

function rankingRepoGetBobotAhp(mysqli $conn): array
{
    $bobot = [];

    foreach (db_all($conn, "
        SELECT b.id_kriteria, b.bobot, b.status_konsistensi, k.nama_kriteria
        FROM bobot_ahp b
        JOIN kriteria k ON k.id_kriteria = b.id_kriteria
        ORDER BY k.id_kriteria ASC
    ") as $row) {
        $bobot[(int) $row['id_kriteria']] = $row;
    }

    return $bobot;
}

function rankingRepoGetBobotAhpLaporan(mysqli $conn): array
{
    return db_all($conn, '
        SELECT b.*, k.kode_kriteria, k.nama_kriteria
        FROM bobot_ahp b
        JOIN kriteria k ON b.id_kriteria = k.id_kriteria
        ORDER BY k.id_kriteria ASC
    ');
}

function rankingRepoGetPerbandinganKriteria(mysqli $conn): array
{
    $perbandingan = [];

    foreach (db_all($conn, 'SELECT id_kriteria_1, id_kriteria_2, nilai FROM perbandingan_ahp') as $row) {
        $perbandingan[(int) $row['id_kriteria_1']][(int) $row['id_kriteria_2']] = (float) $row['nilai'];
    }

    return $perbandingan;
}

function rankingRepoGetPenilaianSupplier(mysqli $conn): array
{
    $penilaian = [];

    foreach (db_all($conn, 'SELECT id_kriteria, id_supplier, skor FROM penilaian_supplier') as $row) {
        $penilaian[(int) $row['id_kriteria']][(int) $row['id_supplier']] = (int) $row['skor'];
    }

    return $penilaian;
}

function rankingRepoGetSkorSupplierLaporan(mysqli $conn): array
{
    return db_all($conn, '
        SELECT p.id_kriteria, k.nama_kriteria, p.id_supplier, s.nama_supplier, p.skor
        FROM penilaian_supplier p
        JOIN kriteria k ON k.id_kriteria = p.id_kriteria
        JOIN supplier s ON s.id_supplier = p.id_supplier
        ORDER BY k.id_kriteria ASC, s.nama_supplier ASC
    ');
}

function rankingRepoGetRankingTersimpan(mysqli $conn): array
{
    return db_all($conn, "
        SELECT h.*, s.nama_supplier, s.jenis_material, s.no_telepon
        FROM hasil_ranking h
        JOIN supplier s ON h.id_supplier = s.id_supplier
        ORDER BY h.ranking ASC
    ");
}

function rankingRepoSimpanHasil(mysqli $conn, array $skorPerKriteria, array $ranking): void
{
    mysqli_begin_transaction($conn);

    try {
        db_exec($conn, 'DELETE FROM penilaian_supplier');
        db_exec($conn, 'DELETE FROM hasil_ranking');

        foreach ($skorPerKriteria as $idKriteria => $skorSupplier) {
            foreach ($skorSupplier as $idSupplier => $skor) {
                db_exec($conn, 'INSERT INTO penilaian_supplier (id_kriteria, id_supplier, skor) VALUES (?, ?, ?)', 'iii', [(int) $idKriteria, (int) $idSupplier, (int) $skor]);
            }
        }

        foreach ($ranking as $data) {
            db_exec($conn, 'INSERT INTO hasil_ranking (id_supplier, nilai, ranking) VALUES (?, ?, ?)', 'idi', [$data['id_supplier'], $data['nilai'], $data['ranking']]);
        }

        mysqli_commit($conn);
    } catch (Throwable $th) {
        mysqli_rollback($conn);
        throw $th;
    }
}
