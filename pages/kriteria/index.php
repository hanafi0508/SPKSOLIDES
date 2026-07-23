<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$data = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="page-toolbar">
        <div>
            <h3>Data Kriteria</h3>
        </div>
        <a href="tambah.php" class="btn btn-primary">Tambah Kriteria</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th width="70">No</th>
                            <th width="120">Kode</th>
                            <th>Nama</th>
                            <th width="140">Jenis</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><strong><?= htmlspecialchars($row['kode_kriteria']); ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_kriteria']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $row['jenis_kriteria']=='benefit'?'success':'warning text-dark'; ?>">
                                <?= ucfirst(htmlspecialchars($row['jenis_kriteria'])); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['id_kriteria']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_kriteria']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../../layouts/footer.php'; ?>
