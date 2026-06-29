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

$skalaFuzzy = [];
$querySkala = mysqli_query($conn, "SELECT * FROM skala_fuzzy ORDER BY id_skala ASC");

while ($row = mysqli_fetch_assoc($querySkala)) {
    $skalaFuzzy[] = $row;
}

$dataPerbandingan = [];
$workflowStatus = null;

if ($id_proyek > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM perbandingan_fahp WHERE id_proyek = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_proyek);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $selected = '';

        foreach ($skalaFuzzy as $skala) {
            $directMatch = abs((float) $row['nilai_l'] - (float) $skala['nilai_l']) < 0.000001
                && abs((float) $row['nilai_m'] - (float) $skala['nilai_m']) < 0.000001
                && abs((float) $row['nilai_u'] - (float) $skala['nilai_u']) < 0.000001;

            $reciprocalMatch = abs((float) $row['nilai_l'] - (1 / (float) $skala['nilai_u'])) < 0.000001
                && abs((float) $row['nilai_m'] - (1 / (float) $skala['nilai_m'])) < 0.000001
                && abs((float) $row['nilai_u'] - (1 / (float) $skala['nilai_l'])) < 0.000001;

            if ($directMatch) {
                $selected = 'P-' . $skala['id_skala'];
                break;
            }

            if ($reciprocalMatch) {
                $selected = 'N-' . $skala['id_skala'];
                break;
            }
        }

        $row['selected_option'] = $selected;
        $dataPerbandingan[$row['id_kriteria_1']][$row['id_kriteria_2']] = $row;
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
            <h4>Input Perbandingan F-AHP</h4>
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
                Bobot F-AHP ini dipakai saat ranking bersama data penilaian supplier.
            </div>
        <?php endif; ?>

        <?php if (count($kriteria) < 2) : ?>

            <div class="alert alert-warning">
                Data kriteria minimal harus 2 untuk melakukan perhitungan F-AHP.
            </div>

        <?php elseif (count($skalaFuzzy) == 0) : ?>

            <div class="alert alert-danger">
                Data skala fuzzy belum tersedia. Silakan isi tabel skala_fuzzy terlebih dahulu.
            </div>

        <?php else : ?>

            <div class="card">
                <div class="card-header bg-info text-white">
                    Matriks Linguistik F-AHP
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        Pilih skala linguistik untuk setiap pasangan kriteria. Opsi "kolom lebih penting" akan otomatis disimpan sebagai bilangan fuzzy reciprocal yang benar.
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

                                                        <input type="text" class="form-control text-center" value="SP (1,1,1)" readonly>

                                                    <?php elseif ($i < $j) : ?>

                                                        <?php
                                                        $selectedSkala = $dataPerbandingan[$id1][$id2]['selected_option'] ?? '';
                                                        ?>

                                                        <select
                                                            name="perbandingan[<?= $id1; ?>][<?= $id2; ?>]"
                                                            class="form-select skala-fuzzy"
                                                            data-target="reciprocal-<?= $id2; ?>-<?= $id1; ?>"
                                                            required>
                                                            <option value="">-- Pilih --</option>

                                                            <?php foreach ($skalaFuzzy as $s) : ?>
                                                                <option
                                                                    value="P-<?= $s['id_skala']; ?>"
                                                                    data-l="<?= $s['nilai_l']; ?>"
                                                                    data-m="<?= $s['nilai_m']; ?>"
                                                                    data-u="<?= $s['nilai_u']; ?>"
                                                                    <?= ($selectedSkala === 'P-' . $s['id_skala']) ? 'selected' : ''; ?>>
                                                                    Baris lebih penting: <?= htmlspecialchars($s['kode']); ?> -
                                                                    <?= htmlspecialchars($s['keterangan']); ?>
                                                                    (<?= $s['nilai_l']; ?>, <?= $s['nilai_m']; ?>, <?= $s['nilai_u']; ?>)
                                                                </option>
                                                                <option
                                                                    value="N-<?= $s['id_skala']; ?>"
                                                                    data-l="<?= 1 / (float) $s['nilai_u']; ?>"
                                                                    data-m="<?= 1 / (float) $s['nilai_m']; ?>"
                                                                    data-u="<?= 1 / (float) $s['nilai_l']; ?>"
                                                                    <?= ($selectedSkala === 'N-' . $s['id_skala']) ? 'selected' : ''; ?>>
                                                                    Kolom lebih penting: <?= htmlspecialchars($s['kode']); ?> -
                                                                    <?= htmlspecialchars($s['keterangan']); ?>
                                                                    (<?= number_format(1 / (float) $s['nilai_u'], 4); ?>, <?= number_format(1 / (float) $s['nilai_m'], 4); ?>, <?= number_format(1 / (float) $s['nilai_l'], 4); ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                    <?php else : ?>

                                                        <?php
                                                        $nilaiAtas = $dataPerbandingan[$id2][$id1] ?? null;

                                                        if ($nilaiAtas) {
                                                            $l = 1 / (float) $nilaiAtas['nilai_u'];
                                                            $m = 1 / (float) $nilaiAtas['nilai_m'];
                                                            $u = 1 / (float) $nilaiAtas['nilai_l'];
                                                            $textReciprocal = number_format($l, 4) . ', ' . number_format($m, 4) . ', ' . number_format($u, 4);
                                                        } else {
                                                            $textReciprocal = '1.0000, 1.0000, 1.0000';
                                                        }
                                                        ?>

                                                        <input
                                                            type="text"
                                                            id="reciprocal-<?= $id1; ?>-<?= $id2; ?>"
                                                            class="form-control text-center bg-light"
                                                            value="<?= $textReciprocal; ?>"
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
                                Proses F-AHP
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
document.querySelectorAll('.skala-fuzzy').forEach(function(select) {
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const l = parseFloat(selectedOption.getAttribute('data-l'));
        const m = parseFloat(selectedOption.getAttribute('data-m'));
        const u = parseFloat(selectedOption.getAttribute('data-u'));
        const target = document.getElementById(this.getAttribute('data-target'));

        if (target && l > 0 && m > 0 && u > 0) {
            target.value = (1 / u).toFixed(4) + ', ' + (1 / m).toFixed(4) + ', ' + (1 / l).toFixed(4);
        }
    });
});
</script>

<?php include "../../layouts/footer.php"; ?>
