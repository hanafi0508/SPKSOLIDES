<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/fahp_function.php';

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

$skalaFuzzy = [];
$querySkala = mysqli_query($conn, "SELECT * FROM skala_fuzzy ORDER BY id_skala ASC");

while ($row = mysqli_fetch_assoc($querySkala)) {
    $skalaFuzzy[$row['id_skala']] = $row;
}

if (count($skalaFuzzy) == 0) {
    echo "<script>alert('Data skala fuzzy belum tersedia!'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$perbandingan = [];
$dataSimpan = [];
$jumlahPerbandingan = 0;

foreach ($inputPerbandingan as $id_kriteria_1 => $data) {
    $id_kriteria_1 = (int) $id_kriteria_1;

    foreach ($data as $id_kriteria_2 => $pilihanSkala) {
        $id_kriteria_2 = (int) $id_kriteria_2;
        $pilihanSkala = trim((string) $pilihanSkala);

        if ($id_kriteria_1 <= 0 || $id_kriteria_2 <= 0 || $pilihanSkala === '') {
            continue;
        }

        [$arah, $idSkalaString] = array_pad(explode('-', $pilihanSkala, 2), 2, '');
        $id_skala = (int) $idSkalaString;

        if (!isset($skalaFuzzy[$id_skala])) {
            continue;
        }

        if ($arah === 'N') {
            $nilai_l = 1 / (float) $skalaFuzzy[$id_skala]['nilai_u'];
            $nilai_m = 1 / (float) $skalaFuzzy[$id_skala]['nilai_m'];
            $nilai_u = 1 / (float) $skalaFuzzy[$id_skala]['nilai_l'];
        } else {
            $nilai_l = (float) $skalaFuzzy[$id_skala]['nilai_l'];
            $nilai_m = (float) $skalaFuzzy[$id_skala]['nilai_m'];
            $nilai_u = (float) $skalaFuzzy[$id_skala]['nilai_u'];
        }

        $perbandingan[$id_kriteria_1][$id_kriteria_2] = [
            'l' => $nilai_l,
            'm' => $nilai_m,
            'u' => $nilai_u
        ];
        $jumlahPerbandingan++;

        $dataSimpan[] = [
            'id_kriteria_1' => $id_kriteria_1,
            'id_kriteria_2' => $id_kriteria_2,
            'id_skala' => $id_skala,
            'nilai_l' => $nilai_l,
            'nilai_m' => $nilai_m,
            'nilai_u' => $nilai_u
        ];
    }
}

if (empty($perbandingan)) {
    echo "<script>alert('Data perbandingan F-AHP belum diisi!'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$jumlahWajib = (count($kriteria) * (count($kriteria) - 1)) / 2;
if ($jumlahPerbandingan !== $jumlahWajib) {
    echo "<script>alert('Semua pasangan kriteria F-AHP wajib diisi.'); window.location='input_perbandingan.php?id_proyek=$id_proyek';</script>";
    exit;
}

$hasil = hitungFAHP($kriteria, $perbandingan);

mysqli_begin_transaction($conn);

try {
    $stmtDeletePerbandingan = mysqli_prepare($conn, "DELETE FROM perbandingan_fahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtDeletePerbandingan, "i", $id_proyek);

    if (!mysqli_stmt_execute($stmtDeletePerbandingan)) {
        throw new Exception("Gagal menghapus data perbandingan F-AHP lama.");
    }

    $stmtDeleteBobot = mysqli_prepare($conn, "DELETE FROM bobot_fahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmtDeleteBobot, "i", $id_proyek);

    if (!mysqli_stmt_execute($stmtDeleteBobot)) {
        throw new Exception("Gagal menghapus data bobot F-AHP lama.");
    }

    foreach ($dataSimpan as $data) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO perbandingan_fahp
            (id_proyek, id_kriteria_1, id_kriteria_2, id_skala, nilai_l, nilai_m, nilai_u)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "iiiiddd",
            $id_proyek,
            $data['id_kriteria_1'],
            $data['id_kriteria_2'],
            $data['id_skala'],
            $data['nilai_l'],
            $data['nilai_m'],
            $data['nilai_u']
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan perbandingan F-AHP.");
        }
    }

    foreach ($kriteria as $k) {
        $id_kriteria = (int) $k['id_kriteria'];
        $bobot = round($hasil['bobot'][$id_kriteria], 6);

        $stmt = mysqli_prepare($conn, "
            INSERT INTO bobot_fahp
            (id_proyek, id_kriteria, bobot)
            VALUES (?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "iid",
            $id_proyek,
            $id_kriteria,
            $bobot
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan bobot F-AHP.");
        }
    }

    mysqli_commit($conn);

    echo "<script>
        alert('Perhitungan F-AHP berhasil disimpan!');
        window.location='hasil.php?id_proyek=$id_proyek';
    </script>";
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);

    echo "<script>
        alert('Terjadi kesalahan saat memproses F-AHP.');
        window.location='input_perbandingan.php?id_proyek=$id_proyek';
    </script>";
    exit;
}
?>
