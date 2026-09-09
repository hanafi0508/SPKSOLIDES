<?php
require_once '../../functions/init.php';
require_once '../../functions/ranking_function.php';
check_login();

$laporan = getLaporanData($conn);
$supplier = $laporan['supplier'];
$kriteria = $laporan['kriteria'];
$bobotAhp = $laporan['bobot_ahp'];
$skorSupplier = $laporan['skor_supplier'];
$rankingAhp = $laporan['ranking_ahp'];
$rekomendasiAhp = $rankingAhp[0] ?? null;

$skor = [];
foreach ($skorSupplier as $row) {
    $skor[$row['id_kriteria']][$row['id_supplier']] = $row['skor'];
}

$bobotById = [];
foreach ($bobotAhp as $b) {
    $bobotById[$b['id_kriteria']] = $b['bobot'];
}
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
        <a href="../dashboard.php" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="judul-laporan">
        <h4>LAPORAN HASIL KEPUTUSAN</h4>
        <h5>PEMILIHAN SUPPLIER TERBAIK</h5>
        <p>Metode Analytical Hierarchy Process (AHP)</p>
        <hr>
    </div>

    <div class="section-title">A. Daftar Supplier</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Nama Supplier</th>
                <th>Barang</th>
                <th>Kontak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($supplier) == 0) : ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada supplier.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($supplier as $s) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= h($s['nama_supplier']); ?></td>
                        <td><?= h($s['jenis_material'] ?? '-'); ?></td>
                        <td><?= h($s['no_telepon'] ?? '-'); ?></td>
                        <td class="text-center"><?= h(ucfirst($s['status'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">B. Daftar Kriteria</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($kriteria) == 0) : ?>
                <tr>
                    <td colspan="4" class="text-center">Data kriteria belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php $no = 1; ?>
                <?php foreach ($kriteria as $k) : ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><?= h($k['kode_kriteria']); ?></td>
                        <td><?= h($k['nama_kriteria']); ?></td>
                        <td><?= h($k['deskripsi'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">C. Bobot Kriteria</div>
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
                        <td class="text-center"><?= h($b['kode_kriteria']); ?></td>
                        <td><?= h($b['nama_kriteria']); ?></td>
                        <td class="text-center"><?= number_format((float) $b['bobot'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">CR</th>
                    <td class="text-center"><?= number_format((float) ($bobotAhp[0]['cr'] ?? 0), 6); ?></td>
                </tr>
                <tr>
                    <th colspan="3">Status Konsistensi</th>
                    <td class="text-center"><?= h($bobotAhp[0]['status_konsistensi'] ?? '-'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">D. Skor Penilaian Supplier & Nilai Akhir</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>Supplier</th>
                <?php foreach ($bobotAhp as $b) : ?>
                    <th>
                        <?= h($b['nama_kriteria']); ?>
                        <br><small>(Bobot: <?= number_format((float) $b['bobot'], 6); ?>)</small>
                    </th>
                <?php endforeach; ?>
                <th>Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rankingAhp) == 0) : ?>
                <tr>
                    <td colspan="<?= count($bobotAhp) + 2; ?>" class="text-center">Penilaian supplier belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($rankingAhp as $r) : ?>
                    <tr>
                        <td><?= h($r['nama_supplier']); ?></td>
                        <?php foreach ($bobotAhp as $b) :
                            $skorVal = $skor[$b['id_kriteria']][$r['id_supplier']] ?? null;
                        ?>
                            <td class="text-center"><?= $skorVal !== null ? h($skorVal) : '-'; ?></td>
                        <?php endforeach; ?>
                        <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">E. Ranking Supplier</div>
    <table class="table table-bordered table-striped">
        <thead class="text-center">
            <tr>
                <th>Ranking</th>
                <th>Nama Supplier</th>
                <th>Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rankingAhp) == 0) : ?>
                <tr>
                    <td colspan="3" class="text-center">Ranking belum tersedia.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($rankingAhp as $r) : ?>
                    <tr>
                        <td class="text-center"><?= $r['ranking']; ?></td>
                        <td><?= h($r['nama_supplier']); ?></td>
                        <td class="text-center"><?= number_format((float) $r['nilai'], 6); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

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