<?php
require_once '../../functions/init.php';
require_auth('admin');

$data = db_all($conn, 'SELECT * FROM users');

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h3>Data User</h3>
        <p class="text-muted mb-0">Kelola akun admin dan pimpinan.</p>
    </div>
    <a href="tambah.php" class="btn btn-primary">Tambah User</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th width="70">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th width="130">Level</th>
                        <th width="170">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= h($row['nama_user']); ?></td>
                        <td><?= h($row['username']); ?></td>
                        <td class="text-center"><span class="badge bg-primary"><?= h($row['level']); ?></span></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['id_user']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php layout_bottom(); ?>