<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek");
$id_proyek = isset($_GET['proyek']) ? (int)$_GET['proyek'] : 0;

$alternatif = [];
$kriteria = [];
$nilai = [];
$workflowStatus = null;

if ($id_proyek > 0) {

    $alternatif = mysqli_query($conn, "
        SELECT a.id_alternatif, s.nama_supplier 
        FROM alternatif a
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE a.id_proyek = $id_proyek
    ");

    $kriteria = mysqli_query($conn, "SELECT * FROM kriteria");

    $data_nilai = mysqli_query($conn, "
        SELECT * FROM penilaian_supplier 
        WHERE id_proyek=$id_proyek
    ");

    while ($n = mysqli_fetch_assoc($data_nilai)) {
        $nilai[$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
    }

    $workflowStatus = getProjectWorkflowStatus($conn, $id_proyek);
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="page-toolbar">
        <div>
            <h3>Penilaian Supplier</h3>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Nilai harus bilangan bulat antara 1 sampai 10</div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Data berhasil disimpan</div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <label>Pilih Proyek</label>
                <select name="proyek" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Proyek --</option>
                    <?php while($p = mysqli_fetch_assoc($proyek)): ?>
                        <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek==$p['id_proyek']?'selected':''; ?>>
                            <?= htmlspecialchars($p['nama_proyek']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($id_proyek): ?>
        <?php if ($workflowStatus): ?>
            <div class="alert alert-info">
                Supplier dipilih: <strong><?= $workflowStatus['alternatif']; ?></strong>,
                kriteria: <strong><?= $workflowStatus['kriteria']; ?></strong>,
                penilaian terisi: <strong><?= $workflowStatus['penilaian_terisi']; ?>/<?= $workflowStatus['penilaian_harus']; ?></strong>.
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($alternatif) == 0): ?>
            <div class="alert alert-warning">Belum ada alternatif</div>
        <?php elseif (mysqli_num_rows($kriteria) == 0): ?>
            <div class="alert alert-warning">Belum ada kriteria</div>
        <?php else: ?>

        <form method="POST" action="simpan.php">

            <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">

            <div class="card mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <?php while($k = mysqli_fetch_assoc($kriteria)): ?>
                                        <th><?= htmlspecialchars($k['nama_kriteria']); ?></th>
                                    <?php endwhile; ?>
                                </tr>
                            </thead>
                            <tbody>

                <?php
                mysqli_data_seek($kriteria, 0);
                while($a = mysqli_fetch_assoc($alternatif)):
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['nama_supplier']); ?></strong></td>

                    <?php while($k = mysqli_fetch_assoc($kriteria)): 
                        $val = $nilai[$a['id_alternatif']][$k['id_kriteria']] ?? '';
                    ?>
                        <td>
                            <input type="number" min="1" max="10" step="1"
                            name="nilai[<?= $a['id_alternatif']; ?>][<?= $k['id_kriteria']; ?>]"
                            value="<?= htmlspecialchars((string) $val); ?>"
                            class="form-control"
                            required>
                        </td>
                    <?php endwhile; ?>

                </tr>
                <?php 
                mysqli_data_seek($kriteria, 0);
                endwhile; 
                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Penilaian</button>

        </form>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
