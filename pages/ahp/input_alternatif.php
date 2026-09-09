<?php
require_once '../../functions/init.php';
require_once '../../functions/ranking_function.php';
require_auth('admin');

$supplier = rankingRepoGetSupplierAktif($conn);
$kriteria = rankingRepoGetKriteria($conn);
$bobotTersimpan = rankingRepoGetBobotAhpLaporan($conn);
$skorTersimpan = rankingRepoGetPenilaianSupplier($conn);

$bobotById = [];
$bobotAda = false;
$bobotKonsisten = false;
foreach ($bobotTersimpan as $b) {
    $bobotById[$b['id_kriteria']] = $b['bobot'];
    $bobotAda = true;
    if (($b['status_konsistensi'] ?? '') === 'konsisten') {
        $bobotKonsisten = true;
    }
}

$jumlahSupplier = count($supplier);
$jumlahKriteria = count($kriteria);

// Hitung sel skor yang belum terisi per supplier
$belumDinilai = [];
foreach ($supplier as $idSupplier => $s) {
    $terisiSupplier = 0;
    foreach ($kriteria as $k) {
        if (isset($skorTersimpan[$k['id_kriteria']][$idSupplier])) {
            $terisiSupplier++;
        }
    }
    $belumDinilai[$idSupplier] = $jumlahKriteria - $terisiSupplier;
}

// Supplier yang belum lengkap skornya tampil paling atas
uasort($supplier, function ($a, $b) use ($belumDinilai) {
    $ka = $belumDinilai[$a['id_supplier']] ?? 0;
    $kb = $belumDinilai[$b['id_supplier']] ?? 0;
    if ($ka !== $kb) {
        return $kb <=> $ka;
    }
    return strnatcmp($a['nama_supplier'], $b['nama_supplier']);
});

// Kondisi yang benar-benar menghalangi penilaian
$blokir = [];
if ($jumlahSupplier < 2) {
    $blokir[] = 'Minimal 2 supplier <strong>berstatus aktif</strong>. Tambah supplier aktif lewat menu <strong>Supplier</strong>.';
}
if ($jumlahKriteria < 2) {
    $blokir[] = 'Minimal 2 <strong>kriteria</strong>. Tambah kriteria lewat menu <strong>Kriteria</strong>.';
}
if (!$bobotAda) {
    $blokir[] = '<strong>Bobot kriteria belum dihitung.</strong> Buka menu <strong>Kriteria</strong> → panel "Bobot Kriteria (AHP)", isi perbandingan pasangan kriteria, lalu klik <strong>Hitung Bobot</strong>.';
} elseif (!$bobotKonsisten) {
    $blokir[] = '<strong>Bobot kriteria belum konsisten</strong> (rasio konsistensi/CR &gt; 0.1). Perbaiki nilai perbandingan di <strong>Kriteria → Bobot Kriteria (AHP)</strong> sampai tanggal konsisten (CR ≤ 0.1).';
}

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="fw-bold">Penilaian Supplier</h4>
    </div>
</div>

<?php if ($blokir): ?>
    <div class="alert alert-danger">
        <strong>Penilaian belum bisa dilakukan:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($blokir as $b): ?>
                <li><?= $b; ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="mb-0 mt-2 small">Setelah semua kondisi dipenuhi, halaman ini otomatis menampilkan form skor.</p>
    </div>
<?php else: ?>

    <div class="card" id="skor">
        <div class="card-header bg-success text-white">
            <strong>Skor Supplier (1–9)</strong>
            <small class="float-end">1 = sangat kurang, 5 = cukup, 9 = sangat baik</small>
        </div>
        <div class="card-body">

            <form method="POST" action="proses_penilaian.php" id="formPenilaian">
                <?php csrf_field(); ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Supplier</th>
                                <?php foreach ($kriteria as $k): ?>
                                    <th>
                                        <?= h($k['kode_kriteria']); ?>
                                        <br><small>(<?= number_format((float) ($bobotById[$k['id_kriteria']] ?? 0), 4); ?>)</small>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supplier as $idSupplier => $s): ?>
                            <?php $kurang = $belumDinilai[$idSupplier]; ?>
                            <tr>
                                <th class="text-start">
                                    <?= h($s['nama_supplier']); ?>
                                    <?php if ($kurang > 0): ?>
                                        <span class="badge bg-warning text-dark">belum dinilai (<?= $kurang; ?>)</span>
                                    <?php endif; ?>
                                </th>
                                <?php foreach ($kriteria as $k):
                                    $idKriteria = (int) $k['id_kriteria'];
                                    $skor = (int) ($skorTersimpan[$idKriteria][$idSupplier] ?? 0);
                                ?>
                                    <td style="min-width:90px" class="<?= $skor === 0 ? 'bg-warning bg-opacity-25' : ''; ?>">
                                        <select name="skor[<?= $idKriteria; ?>][<?= $idSupplier; ?>]" class="form-select" required>
                                            <option value="">-</option>
                                            <?php for ($sk = 1; $sk <= 9; $sk++): ?>
                                                <option value="<?= $sk; ?>" <?= $skor === $sk ? 'selected' : ''; ?>><?= $sk; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-icon-gap"><i class="bi bi-trophy"></i> Simpan &amp; Hitung Ranking</button>
                    <a href="../dashboard.php" class="btn btn-secondary">Lihat Hasil</a>
                </div>
            </form>

        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("formPenilaian");
        if (!form) return;
        var btn = form.querySelector("button[type=submit]");

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (btn.disabled) return;
            btn.disabled = true;

            fetch("proses_penilaian.php", {
                method: "POST",
                body: new FormData(form),
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showToast(data.message, data.success ? "success" : "error");
            })
            .catch(function () {
                showToast("Terjadi kesalahan koneksi, coba lagi.", "error");
            })
            .finally(function () { btn.disabled = false; });
        });
    });
    </script>

<?php endif; ?>
<?php layout_bottom(); ?>