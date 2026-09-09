<?php
require_once '../../functions/init.php';
require_once '../../functions/ranking_function.php';
require_auth('admin');

$data = db_all($conn, 'SELECT * FROM kriteria ORDER BY kode_kriteria ASC');
$dataPerbandingan = rankingRepoGetPerbandinganKriteria($conn);
$bobotTersimpan = rankingRepoGetBobotAhpLaporan($conn);

$opsiAHP = [
    '9' => '9 - Baris mutlak lebih penting',
    '7' => '7 - Baris sangat lebih penting',
    '5' => '5 - Baris lebih penting',
    '3' => '3 - Baris cukup lebih penting',
    '1' => '1 - Sama penting',
    '0.333333' => '1/3 - Kolom cukup lebih penting',
    '0.2' => '1/5 - Kolom lebih penting',
    '0.142857' => '1/7 - Kolom sangat lebih penting',
    '0.111111' => '1/9 - Kolom mutlak lebih penting',
];

$bobotById = [];
foreach ($bobotTersimpan as $b) {
    $bobotById[$b['id_kriteria']] = $b['bobot'];
}

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="fw-bold">Data Kriteria</h4>
    </div>
    <button type="button" class="btn btn-primary btn-icon-gap" data-bs-toggle="modal" data-bs-target="#modalKriteria">
        <i class="bi bi-plus-lg"></i> Tambah Kriteria
    </button>
</div>

<?php if (!$data): ?>
    <div class="alert alert-info">Belum ada kriteria. Tambahkan minimal 2 kriteria untuk menghitung bobot AHP.</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th width="100">Kode</th>
                        <th>Nama</th>
                        <th>Bobot</th>
                        <th width="170">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><strong><?= h($row['kode_kriteria']); ?></strong></td>
                        <td><?= h($row['nama_kriteria']); ?></td>
                        <td class="text-center">
                            <?php $bwt = $bobotById[$row['id_kriteria']] ?? null; ?>
                            <?php if ($bwt !== null): ?>
                                <span class="badge bg-primary"><?= number_format((float) $bwt, 6); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalKriteria"
                                    data-id="<?= (int) $row['id_kriteria']; ?>"
                                    data-kode="<?= h($row['kode_kriteria']); ?>"
                                    data-nama="<?= h($row['nama_kriteria']); ?>"
                                    data-deskripsi="<?= h($row['deskripsi'] ?? ''); ?>">Edit</button>
                            <a href="hapus.php?id=<?= (int) $row['id_kriteria']; ?>" class="btn btn-danger btn-sm"
                               data-confirm="Yakin hapus kriteria <?= h($row['kode_kriteria']); ?>?">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============ PANEL BOBOT KRITERIA (AHP) ============ -->
<div class="card mt-4" id="bobot">
    <div class="card-header bg-info text-white">
        <strong>Bobot Kriteria (AHP)</strong>
        <small class="float-end">Bandingkan pasangan kriteria → bobot terhitung otomatis</small>
    </div>
    <div class="card-body">

        <?php if (count($data) < 2): ?>
            <div class="alert alert-warning mb-0">Minimal 2 kriteria untuk menghitung bobot AHP.</div>
        <?php else: ?>

            <form method="POST" action="proses_bobot.php">
                <?php csrf_field(); ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Kriteria</th>
                                <?php foreach ($data as $k): ?>
                                    <th><?= h($k['kode_kriteria']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($data); $i++): ?>
                            <tr>
                                <th class="text-start">
                                    <?= h($data[$i]['kode_kriteria']); ?> - <?= h($data[$i]['nama_kriteria']); ?>
                                </th>
                                <?php for ($j = 0; $j < count($data); $j++):
                                    $id1 = $data[$i]['id_kriteria'];
                                    $id2 = $data[$j]['id_kriteria'];
                                ?>
                                <td>
                                    <?php if ($i == $j): ?>
                                        <input type="text" class="form-control text-center" value="1" readonly>
                                    <?php elseif ($i < $j):
                                        $nilai = (string) ($dataPerbandingan[$id1][$id2] ?? '1');
                                    ?>
                                        <select name="perbandingan[<?= $id1; ?>][<?= $id2; ?>]"
                                                class="form-select nilai-ahp"
                                                data-target="reciprocal-<?= $id2; ?>-<?= $id1; ?>" required>
                                            <?php foreach ($opsiAHP as $value => $label): ?>
                                                <option value="<?= $value; ?>" <?= (float) $nilai === (float) $value ? 'selected' : ''; ?>>
                                                    <?= h($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else:
                                        $nilaiAtas = (float) ($dataPerbandingan[$id2][$id1] ?? 1);
                                        $reciprocal = $nilaiAtas > 0 ? 1 / $nilaiAtas : 1;
                                    ?>
                                        <input type="text" id="reciprocal-<?= $id1; ?>-<?= $id2; ?>"
                                               class="form-control text-center bg-light"
                                               value="<?= number_format($reciprocal, 6); ?>" readonly>
                                    <?php endif; ?>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-info btn-icon-gap"><i class="bi bi-calculator"></i> Hitung Bobot</button>
            </form>

            <?php if ($bobotTersimpan): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Hasil Bobot Kriteria</h6>
                    <div class="row g-2 align-items-center">
                        <?php foreach ($bobotTersimpan as $b): ?>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted"><?= h($b['kode_kriteria']); ?></div>
                                    <strong><?= number_format((float) $b['bobot'], 6); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2">
                        <strong>CR:</strong> <?= number_format((float) ($bobotTersimpan[0]['cr'] ?? 0), 6); ?>
                        <span class="ms-2">
                            <?php if (($bobotTersimpan[0]['status_konsistensi'] ?? '') == 'konsisten'): ?>
                                <span class="badge bg-success">Konsisten</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Tidak Konsisten</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- ============ MODAL TAMBAH / EDIT KRITERIA ============ -->
<div class="modal fade" id="modalKriteria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="simpan.php" id="formKriteria">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="kr_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKrTitle">Tambah Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Kode</label>
                        <input type="text" name="kode" id="kr_kode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" id="kr_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="kr_deskripsi" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button name="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.nilai-ahp').forEach(function(select) {
    select.addEventListener('change', function() {
        const target = document.getElementById(this.getAttribute('data-target'));
        const nilai = parseFloat(this.value);
        if (target && nilai > 0) target.value = (1 / nilai).toFixed(6);
    });
});

document.getElementById('modalKriteria').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const hasData = btn.dataset.id;
    const title = document.getElementById('modalKrTitle');
    document.getElementById('kr_id').value = hasData || '';
    document.getElementById('kr_kode').value = btn.dataset.kode || '';
    document.getElementById('kr_nama').value = btn.dataset.nama || '';
    document.getElementById('kr_deskripsi').value = btn.dataset.deskripsi || '';
    title.textContent = hasData ? 'Edit Kriteria' : 'Tambah Kriteria';
});

document.getElementById('modalKriteria').addEventListener('hidden.bs.modal', function() {
    document.getElementById('kr_id').value = '';
    document.getElementById('formKriteria').reset();
});
</script>
<?php layout_bottom(); ?>
