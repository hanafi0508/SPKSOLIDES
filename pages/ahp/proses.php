<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ahp_function.php';

check_login();
check_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: input_perbandingan.php");
    exit;
}

$id_proyek = isset($_POST['id_proyek']) ? (int) $_POST['id_proyek'] : 0;
$inputPerbandingan = $_POST['perbandingan'] ?? [];

if ($id_proyek <= 0) {
    echo "<script>alert('Proyek belum dipilih!'); window.location='input_perbandingan.php';</script>";
    exit;
}

$kriteria = [];
$queryKriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

while ($row = mysqli_fetch_assoc($queryKriteria)) {
    $kriteria[] = $row;
}

if (count($kriteria) < 2) {
    echo "<script>alert('Data kriteria minimal harus 2!'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$perbandingan = [];
$jumlahPerbandingan = 0;

foreach ($inputPerbandingan as $id_kriteria_1 => $data) {
    $id_kriteria_1 = (int) $id_kriteria_1;

    foreach ($data as $id_kriteria_2 => $nilai) {
        $id_kriteria_2 = (int) $id_kriteria_2;
        $nilai = (float) $nilai;

        if ($id_kriteria_1 <= 0 || $id_kriteria_2 <= 0 || $nilai <= 0) {
            continue;
        }

        $perbandingan[$id_kriteria_1][$id_kriteria_2] = $nilai;
        $jumlahPerbandingan++;
    }
}

if (empty($perbandingan)) {
    echo "<script>alert('Data perbandingan belum diisi!'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$jumlahWajib = (count($kriteria) * (count($kriteria) - 1)) / 2;
if ($jumlahPerbandingan !== $jumlahWajib) {
    echo "<script>alert('Semua pasangan kriteria AHP wajib diisi.'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$hasil = hitungAHP($kriteria, $perbandingan);

mysqli_begin_transaction($conn);

try {
    $stmtDeletePerbandingan = mysqli_prepare($conn, "DELETE FROM perbandingan_ahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtDeletePerbandingan, "i", $id_proyek);

    if (!mysqli_stmt_execute($stmtDeletePerbandingan)) {
        throw new Exception("Gagal menghapus data perbandingan lama.");
    }

    $stmtDeleteBobot = mysqli_prepare($conn, "DELETE FROM bobot_ahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtDeleteBobot, "i", $id_proyek);

    if (!mysqli_stmt_execute($stmtDeleteBobot)) {
        throw new Exception("Gagal menghapus data bobot lama.");
    }

    foreach ($perbandingan as $id_kriteria_1 => $data) {
        foreach ($data as $id_kriteria_2 => $nilai) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO perbandingan_ahp 
                (id_proyek, id_kriteria_1, id_kriteria_2, nilai)
                VALUES (?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "iiid",
                $id_proyek,
                $id_kriteria_1,
                $id_kriteria_2,
                $nilai
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal menyimpan perbandingan AHP.");
            }
        }
    }

    foreach ($kriteria as $k) {
        $id_kriteria = (int) $k['id_kriteria'];
        $bobot = round($hasil['bobot'][$id_kriteria], 6);
        $lambda = round($hasil['lambda_maks'], 6);
        $ci = round($hasil['ci'], 6);
        $cr = round($hasil['cr'], 6);
        $status = $hasil['status_konsistensi'];

        $stmt = mysqli_prepare($conn, "
            INSERT INTO bobot_ahp 
            (id_proyek, id_kriteria, bobot, lambda_value, ci, cr, status_konsistensi)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "iidddds",
            $id_proyek,
            $id_kriteria,
            $bobot,
            $lambda,
            $ci,
            $cr,
            $status
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan bobot AHP.");
        }
    }

    mysqli_commit($conn);

    echo "<script>
        alert('Perhitungan AHP berhasil disimpan!');
        window.location='hasil.php?id_proyek=$id_proyek';
    </script>";
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);

    echo "<script>
        alert('Terjadi kesalahan saat memproses AHP.');
        window.location='input_perbandingan.php?id_proyek=$id_proyek';
    </script>";
    exit;
}
?>
