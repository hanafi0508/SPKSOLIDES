<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek ORDER BY id_proyek DESC");
$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;

$kriteria = [];
$queryKriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

while ($row = mysqli_fetch_assoc($queryKriteria)) {
    $kriteria[] = $row;
}

$opsiAHP = [
    '9' => '9 - Baris mutlak lebih penting',
    '8' => '8 - Baris sangat kuat lebih penting',
    '7' => '7 - Baris sangat lebih penting',
    '6' => '6 - Baris kuat lebih penting',
    '5' => '5 - Baris lebih penting',
    '4' => '4 - Baris sedikit lebih penting',
    '3' => '3 - Baris cukup lebih penting',
    '2' => '2 - Baris mendekati cukup penting',
    '1' => '1 - Sama penting',
    '0.5' => '1/2 - Kolom mendekati cukup penting',
    '0.333333' => '1/3 - Kolom cukup lebih penting',
    '0.25' => '1/4 - Kolom sedikit lebih penting',
    '0.2' => '1/5 - Kolom lebih penting',
    '0.166667' => '1/6 - Kolom kuat lebih penting',
    '0.142857' => '1/7 - Kolom sangat lebih penting',
    '0.125' => '1/8 - Kolom sangat kuat lebih penting',
    '0.111111' => '1/9 - Kolom mutlak lebih penting',
];

$dataPerbandingan = [];
$workflowStatus = null;

if ($id_proyek > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM perbandingan_ahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_proyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $dataPerbandingan[$row['id_kriteria_1']][$row['id_kriteria_2']] = (float) $row['nilai'];
    }

    $workflowStatus = getProjectWorkflowStatus($conn, $id_proyek);
}

include "../../layouts/header.php";
include "../../layouts/navbar.php";
include "../../layouts/sidebar.php";
?>

<div class="col-md-10 p-4">

    <div class="row mb-3">
        <div class="col-md-12">
            <h4>Input Perbandingan AHP</h4>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            Pilih Proyek
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-8">
                        <select name="id_proyek" class="form-select" required>
                            <option value="">-- Pilih Proyek --</option>
                            <?php while ($p = mysqli_fetch_assoc($proyek)) : ?>
                                <option value="<?= $p['id_proyek']; ?>" <?= ($id_proyek == $p['id_proyek']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($p['nama_proyek']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            Tampilkan Matriks
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id_proyek > 0) : ?>
        <?php if ($workflowStatus): ?>
            <div class="alert alert-info">
                Supplier di Perhitungan: <strong><?= $workflowStatus['alternatif']; ?></strong>,
                penilaian terisi: <strong><?= $workflowStatus['penilaian_terisi']; ?>/<?= $workflowStatus['penilaian_harus']; ?></strong>.
                Bobot AHP ini dipakai saat ranking bersama data penilaian supplier.
            </div>
        <?php endif; ?>

        <?php if (count($kriteria) < 2) : ?>

            <div class="alert alert-warning">
                Data kriteria minimal harus 2 untuk melakukan perhitungan AHP.
            </div>

        <?php else : ?>

            <div class="card">
                <div class="card-header bg-info text-white">
                    Matriks Perbandingan Berpasangan Kriteria
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        Pilih tingkat kepentingan untuk setiap pasangan kriteria. Opsi pecahan berarti kriteria pada kolom lebih penting dari kriteria pada baris.
                    </div>

                    <form method="POST" action="proses.php">
                        <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kriteria</th>
                                        <?php foreach ($kriteria as $k) : ?>
                                            <th><?= htmlspecialchars($k['kode_kriteria']); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php for ($i = 0; $i < count($kriteria); $i++) : ?>
                                        <tr>
                                            <th class="text-start">
                                                <?= htmlspecialchars($kriteria[$i]['kode_kriteria']); ?> -
                                                <?= htmlspecialchars($kriteria[$i]['nama_kriteria']); ?>
                                            </th>

                                            <?php for ($j = 0; $j < count($kriteria); $j++) : ?>
                                                <?php
                                                $id1 = $kriteria[$i]['id_kriteria'];
                                                $id2 = $kriteria[$j]['id_kriteria'];
                                                ?>

                                                <td>
                                                    <?php if ($i == $j) : ?>
                                                        <input type="text" class="form-control text-center" value="1" readonly>
                                                    <?php elseif ($i < $j) : ?>
                                                        <?php $nilai = (string) ($dataPerbandingan[$id1][$id2] ?? '1'); ?>
                                                        <select
                                                            name="perbandingan[<?= $id1; ?>][<?= $id2; ?>]"
                                                            class="form-select nilai-ahp"
                                                            data-target="reciprocal-<?= $id2; ?>-<?= $id1; ?>"
                                                            required>
                                                            <?php foreach ($opsiAHP as $value => $label) : ?>
                                                                <option value="<?= $value; ?>" <?= ((float) $nilai === (float) $value) ? 'selected' : ''; ?>>
                                                                    <?= htmlspecialchars($label); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else : ?>
                                                        <?php
                                                        $nilaiAtas = (float) ($dataPerbandingan[$id2][$id1] ?? 1);
                                                        $reciprocal = $nilaiAtas > 0 ? 1 / $nilaiAtas : 1;
                                                        ?>
                                                        <input
                                                            type="text"
                                                            id="reciprocal-<?= $id1; ?>-<?= $id2; ?>"
                                                            class="form-control text-center bg-light"
                                                            value="<?= number_format($reciprocal, 6); ?>"
                                                            readonly>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-info">
                                Proses AHP
                            </button>

                            <a href="../dashboard.php" class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
document.querySelectorAll('.nilai-ahp').forEach(function(select) {
    select.addEventListener('change', function() {
        const target = document.getElementById(this.getAttribute('data-target'));
        const nilai = parseFloat(this.value);

        if (target && nilai > 0) {
            target.value = (1 / nilai).toFixed(6);
        }
    });
});
</script>

<?php include "../../layouts/footer.php"; ?>
