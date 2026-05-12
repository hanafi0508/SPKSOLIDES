<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;

$proyekList = mysqli_query($conn, "SELECT * FROM proyek ORDER BY id_proyek DESC");

$proyek = null;
$supplier = [];
$kriteria = [];
$bobotAhp = [];
$bobotFahp = [];
$rankingAhp = [];
$rankingFahp = [];

if ($id_proyek > 0) {
    $laporan = getLaporanData($conn, $id_proyek);
    $proyek = $laporan['proyek'];
    $supplier = $laporan['supplier'];
    $kriteria = $laporan['kriteria'];
    $bobotAhp = $laporan['bobot_ahp'];
    $bobotFahp = $laporan['bobot_fahp'];
    $rankingAhp = $laporan['ranking_ahp'];
    $rankingFahp = $laporan['ranking_fahp'];
}

$mapAhp = [];
foreach ($rankingAhp as $r) {
    $mapAhp[$r['id_alternatif']] = $r;
}

$mapFahp = [];
foreach ($rankingFahp as $r) {
    $mapFahp[$r['id_alternatif']] = $r;
}

$rekomendasiAhp = $rankingAhp[0] ?? null;
$rekomendasiFahp = $rankingFahp[0] ?? null;

include "../../layouts/header.php";
include "../../layouts/navbar.php";
include "../../layouts/sidebar.php";
?>

<div class="col-md-10 p-4">

    <div class="row mb-3">
        <div class="col-md-12">
            <h4>Laporan Hasil Keputusan</h4>
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
                            <?php while ($p = mysqli_fetch_assoc($proyekList)) : ?>
                                <option value="<?= $p['id_proyek']; ?>" <?= ($id_proyek == $p['id_proyek']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($p['nama_proyek']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            Tampilkan Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id_proyek > 0 && !$proyek) : ?>

        <div class="alert alert-danger">
            Data proyek tidak ditemukan.
        </div>

    <?php elseif ($id_proyek > 0 && $proyek) : ?>

        <div class="mb-3">
            <a href="cetak.php?id_proyek=<?= $id_proyek; ?>" target="_blank" class="btn btn-dark">
                Cetak Laporan
            </a>
            <a href="../dashboard.php" class="btn btn-secondary">
                Kembali
            </a>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                Identitas Proyek
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Nama Proyek</th>
                        <td><?= htmlspecialchars($proyek['nama_proyek']); ?></td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td><?= htmlspecialchars($proyek['lokasi'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td><?= htmlspecialchars($proyek['tanggal_mulai'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td><?= htmlspecialchars($proyek['tanggal_selesai'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td><?= htmlspecialchars($proyek['keterangan'] ?? '-'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                Daftar Supplier
            </div>
            <div class="card-body">
                <?php if (count($supplier) == 0) : ?>
                    <div class="alert alert-warning">Belum ada supplier pada proyek ini.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Supplier</th>
                                    <th>Jenis Material</th>
                                    <th>No Telepon</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($supplier as $s) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($s['nama_supplier']); ?></td>
                                        <td><?= htmlspecialchars($s['jenis_material'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($s['no_telepon'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($s['email'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <?php if ($s['status'] == 'aktif') : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                Daftar Kriteria
            </div>
            <div class="card-body">
                <?php if (count($kriteria) == 0) : ?>
                    <div class="alert alert-warning">Data kriteria belum tersedia.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Kriteria</th>
                                    <th>Jenis</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($kriteria as $k) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center"><?= htmlspecialchars($k['kode_kriteria']); ?></td>
                                        <td><?= htmlspecialchars($k['nama_kriteria']); ?></td>
                                        <td class="text-center">
                                            <?php if ($k['jenis_kriteria'] == 'benefit') : ?>
                                                <span class="badge bg-success">Benefit</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning text-dark">Cost</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($k['deskripsi'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        Bobot AHP
                    </div>
                    <div class="card-body">
                        <?php if (count($bobotAhp) == 0) : ?>
                            <div class="alert alert-warning">Bobot AHP belum tersedia.</div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Kriteria</th>
                                            <th>Bobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bobotAhp as $b) : ?>
                                            <tr>
                                                <td class="text-center"><?= htmlspecialchars($b['kode_kriteria']); ?></td>
                                                <td><?= htmlspecialchars($b['nama_kriteria']); ?></td>
                                                <td class="text-center"><?= number_format((float) $b['bobot'], 6); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2">
                                <strong>CR:</strong> <?= number_format((float) ($bobotAhp[0]['cr'] ?? 0), 6); ?>
                                |
                                <strong>Status:</strong>
                                <?php if (($bobotAhp[0]['status_konsistensi'] ?? '') == 'konsisten') : ?>
                                    <span class="badge bg-success">Konsisten</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">Tidak Konsisten</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        Bobot F-AHP
                    </div>
                    <div class="card-body">
                        <?php if (count($bobotFahp) == 0) : ?>
                            <div class="alert alert-warning">Bobot F-AHP belum tersedia.</div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Kriteria</th>
                                            <th>Bobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bobotFahp as $b) : ?>
                                            <tr>
                                                <td class="text-center"><?= htmlspecialchars($b['kode_kriteria']); ?></td>
                                                <td><?= htmlspecialchars($b['nama_kriteria']); ?></td>
                                                <td class="text-center"><?= number_format((float) $b['bobot'], 6); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        Ranking AHP
                    </div>
                    <div class="card-body">
                        <?php if (count($rankingAhp) == 0) : ?>
                            <div class="alert alert-warning">Ranking AHP belum tersedia.</div>
                        <?php else : ?>
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Supplier</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rankingAhp as $r) : ?>
                                        <tr>
                                            <td class="text-center"><?= $r['ranking']; ?></td>
                                            <td><?= htmlspecialchars($r['nama_supplier']); ?></td>
                                            <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        Ranking F-AHP
                    </div>
                    <div class="card-body">
                        <?php if (count($rankingFahp) == 0) : ?>
                            <div class="alert alert-warning">Ranking F-AHP belum tersedia.</div>
                        <?php else : ?>
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Supplier</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rankingFahp as $r) : ?>
                                        <tr>
                                            <td class="text-center"><?= $r['ranking']; ?></td>
                                            <td><?= htmlspecialchars($r['nama_supplier']); ?></td>
                                            <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                Perbandingan Metode
            </div>
            <div class="card-body">
                <?php if (count($rankingAhp) == 0 || count($rankingFahp) == 0) : ?>
                    <div class="alert alert-warning">
                        Perbandingan metode belum bisa ditampilkan karena ranking AHP atau F-AHP belum tersedia.
                    </div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Supplier</th>
                                    <th>Ranking AHP</th>
                                    <th>Ranking F-AHP</th>
                                    <th>Selisih</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($mapAhp as $idAlt => $ahp) : ?>
                                    <?php
                                    $fahp = $mapFahp[$idAlt] ?? null;
                                    $rankAhp = (int) $ahp['ranking'];
                                    $rankFahp = $fahp ? (int) $fahp['ranking'] : 0;
                                    $selisih = $fahp ? abs($rankAhp - $rankFahp) : '-';
                                    $status = ($fahp && $rankAhp == $rankFahp) ? 'Tetap' : 'Berubah';
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($ahp['nama_supplier']); ?></td>
                                        <td class="text-center"><?= $rankAhp; ?></td>
                                        <td class="text-center"><?= $fahp ? $rankFahp : '-'; ?></td>
                                        <td class="text-center"><?= $selisih; ?></td>
                                        <td class="text-center">
                                            <?php if ($status == 'Tetap') : ?>
                                                <span class="badge bg-success">Tetap</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning text-dark">Berubah</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                Rekomendasi Supplier
            </div>
            <div class="card-body">
                <?php if (!$rekomendasiAhp || !$rekomendasiFahp) : ?>

                    <div class="alert alert-warning">
                        Rekomendasi belum tersedia karena ranking AHP atau F-AHP belum lengkap.
                    </div>

                <?php elseif ($rekomendasiAhp['id_alternatif'] == $rekomendasiFahp['id_alternatif']) : ?>

                    <div class="alert alert-success">
                        Supplier rekomendasi utama adalah
                        <strong><?= htmlspecialchars($rekomendasiAhp['nama_supplier']); ?></strong>,
                        karena menempati ranking 1 pada metode AHP dan F-AHP.
                    </div>

                <?php else : ?>

                    <div class="alert alert-warning">
                        Ranking 1 AHP dan F-AHP berbeda, sehingga sistem menampilkan dua kandidat utama.
                    </div>

                    <table class="table table-bordered">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Metode</th>
                                <th>Supplier</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">AHP</td>
                                <td><?= htmlspecialchars($rekomendasiAhp['nama_supplier']); ?></td>
                                <td class="text-center"><?= number_format((float) $rekomendasiAhp['nilai'], 6); ?></td>
                            </tr>
                            <tr>
                                <td class="text-center">F-AHP</td>
                                <td><?= htmlspecialchars($rekomendasiFahp['nama_supplier']); ?></td>
                                <td class="text-center"><?= number_format((float) $rekomendasiFahp['nilai'], 6); ?></td>
                            </tr>
                        </tbody>
                    </table>

                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php include "../../layouts/footer.php"; ?>
