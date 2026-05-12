<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek ORDER BY id_proyek DESC");

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;

$kriteria = [];
$queryKriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

while ($row = mysqli_fetch_assoc($queryKriteria)) {
    $kriteria[] = $row;
}

$dataPerbandingan = [];

if ($id_proyek > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM perbandingan_ahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_proyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $dataPerbandingan[$row['id_kriteria_1']][$row['id_kriteria_2']] = $row['nilai'];
    }
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
                        Isi hanya bagian atas diagonal. Diagonal otomatis bernilai 1, bagian bawah otomatis reciprocal.
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

                                                        <?php
                                                        $nilai = $dataPerbandingan[$id1][$id2] ?? 1;
                                                        ?>

                                                        <select
                                                            name="perbandingan[<?= $id1; ?>][<?= $id2; ?>]"
                                                            class="form-select nilai-ahp"
                                                            data-target="reciprocal-<?= $id2; ?>-<?= $id1; ?>"
                                                            required>
                                                            <?php for ($n = 1; $n <= 9; $n++) : ?>
                                                                <option value="<?= $n; ?>" <?= ((float) $nilai == $n) ? 'selected' : ''; ?>>
                                                                    <?= $n; ?>
                                                                </option>
                                                            <?php endfor; ?>
                                                        </select>

                                                    <?php else : ?>

                                                        <?php
                                                        $nilaiAtas = $dataPerbandingan[$id2][$id1] ?? 1;
                                                        $reciprocal = 1 / (float) $nilaiAtas;
                                                        ?>

                                                        <input
                                                            type="text"
                                                            id="reciprocal-<?= $id1; ?>-<?= $id2; ?>"
                                                            class="form-control text-center bg-light"
                                                            value="<?= number_format($reciprocal, 4); ?>"
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
        let targetId = this.getAttribute('data-target');
        let target = document.getElementById(targetId);
        let nilai = parseFloat(this.value);

        if (target && nilai > 0) {
            target.value = (1 / nilai).toFixed(4);
        }
    });
});
</script>

<?php include "../../layouts/footer.php"; ?>
