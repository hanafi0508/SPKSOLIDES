<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;

if ($id_proyek <= 0) {
    echo "Proyek tidak valid.";
    exit;
}

$laporan = getLaporanData($conn, $id_proyek);
$proyek = $laporan['proyek'];

if (!$proyek) {
    echo "Data proyek tidak ditemukan.";
    exit;
}

$supplier = $laporan['supplier'];
$kriteria = $laporan['kriteria'];
$bobotAhp = $laporan['bobot_ahp'];
$bobotFahp = $laporan['bobot_fahp'];
$rankingAhp = $laporan['ranking_ahp'];
$rankingFahp = $laporan['ranking_fahp'];

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Hasil Keputusan</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .container-print {
            width: 95%;
            margin: 20px auto;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 25px;
        }

        .judul-laporan h4,
        .judul-laporan h5,
        .judul-laporan p {
            margin: 3px 0;
        }

        table {
            font-size: 12px;
        }

        .section-title {
            margin-top: 20px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 15px;
        }

        .signature {
            margin-top: 50px;
            width: 100%;
        }

        .signature td {
            text-align: center;
            height: 120px;
            vertical-align: bottom;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .container-print {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="container-print">

    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark">
            Print
        </button>
        <a href="index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="judul-laporan">
        <h4>LAPORAN HASIL KEPUTUSAN</h4>
        <h5>PEMILIHAN SUPPLIER PROYEK PEMBANGUNAN</h5>
        <p>Perbandingan Metode AHP dan F-AHP</p>
        <hr>
    </div>

    <div class="section-title">A. Identitas Proyek</div>
    <table class="table table-bordered">
        <tr>
            <th width="200">Nama Proyek</th>
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

    <div class="section-title">B. Daftar Supplier</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Nama Supplier</th>
                <th>Tipe</th>
                <th>Barang / Jasa</th>
                <th>Kontak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($supplier) == 0) : ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada data supplier.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($supplier as $s) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($s['nama_supplier']); ?></td>
                        <td><?= ucfirst(htmlspecialchars($s['tipe_supplier'] ?? 'barang')); ?></td>
                        <td><?= htmlspecialchars($s['jenis_material'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($s['no_telepon'] ?? '-'); ?></td>
                        <td class="text-center"><?= htmlspecialchars($s['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">C. Daftar Kriteria</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Jenis</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($kriteria) == 0) : ?>
                <tr>
                    <td colspan="5" class="text-center">Data kriteria belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($kriteria as $k) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><?= htmlspecialchars($k['kode_kriteria']); ?></td>
                        <td><?= htmlspecialchars($k['nama_kriteria']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($k['jenis_kriteria']); ?></td>
                        <td><?= htmlspecialchars($k['deskripsi'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">D. Bobot Kriteria AHP</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($bobotAhp) == 0) : ?>
                <tr>
                    <td colspan="4" class="text-center">Bobot AHP belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($bobotAhp as $b) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><?= htmlspecialchars($b['kode_kriteria']); ?></td>
                        <td><?= htmlspecialchars($b['nama_kriteria']); ?></td>
                        <td class="text-center"><?= number_format((float) $b['bobot'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">CR</th>
                    <td class="text-center"><?= number_format((float) ($bobotAhp[0]['cr'] ?? 0), 6); ?></td>
                </tr>
                <tr>
                    <th colspan="3">Status Konsistensi</th>
                    <td class="text-center"><?= htmlspecialchars($bobotAhp[0]['status_konsistensi'] ?? '-'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">E. Bobot Kriteria F-AHP</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($bobotFahp) == 0) : ?>
                <tr>
                    <td colspan="4" class="text-center">Bobot F-AHP belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($bobotFahp as $b) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><?= htmlspecialchars($b['kode_kriteria']); ?></td>
                        <td><?= htmlspecialchars($b['nama_kriteria']); ?></td>
                        <td class="text-center"><?= number_format((float) $b['bobot'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">F. Ranking Supplier Metode AHP</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>Ranking</th>
                <th>Nama Supplier</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rankingAhp) == 0) : ?>
                <tr>
                    <td colspan="3" class="text-center">Ranking AHP belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($rankingAhp as $r) : ?>
                    <tr>
                        <td class="text-center"><?= $r['ranking']; ?></td>
                        <td><?= htmlspecialchars($r['nama_supplier']); ?></td>
                        <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">G. Ranking Supplier Metode F-AHP</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>Ranking</th>
                <th>Nama Supplier</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rankingFahp) == 0) : ?>
                <tr>
                    <td colspan="3" class="text-center">Ranking F-AHP belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($rankingFahp as $r) : ?>
                    <tr>
                        <td class="text-center"><?= $r['ranking']; ?></td>
                        <td><?= htmlspecialchars($r['nama_supplier']); ?></td>
                        <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">H. Perbandingan Metode</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
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
            <?php if (count($rankingAhp) == 0 || count($rankingFahp) == 0) : ?>
                <tr>
                    <td colspan="6" class="text-center">Perbandingan metode belum tersedia.</td>
                </tr>
            <?php else : ?>
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
                        <td class="text-center"><?= $status; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">I. Rekomendasi Supplier</div>

    <?php if (!$rekomendasiAhp || !$rekomendasiFahp) : ?>

        <p>Rekomendasi belum tersedia karena ranking AHP atau F-AHP belum lengkap.</p>

    <?php elseif ($rekomendasiAhp['id_alternatif'] == $rekomendasiFahp['id_alternatif']) : ?>

        <p>
            Berdasarkan hasil perhitungan metode AHP dan F-AHP, supplier yang direkomendasikan adalah
            <strong><?= htmlspecialchars($rekomendasiAhp['nama_supplier']); ?></strong>,
            karena menempati ranking 1 pada kedua metode.
        </p>

    <?php else : ?>

        <p>
            Berdasarkan hasil perhitungan, ranking 1 metode AHP dan F-AHP berbeda.
            Oleh karena itu, sistem menampilkan dua kandidat supplier utama sebagai berikut:
        </p>

        <table class="table table-bordered">
            <thead class="text-center">
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

    <table class="signature">
        <tr>
            <td width="60%"></td>
            <td>
                Pimpinan<br><br><br><br>
                ___________________________
            </td>
        </tr>
    </table>

</div>

<script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
