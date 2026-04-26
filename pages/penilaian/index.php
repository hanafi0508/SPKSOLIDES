<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek");
$id_proyek = isset($_GET['proyek']) ? (int)$_GET['proyek'] : 0;

$alternatif = [];
$kriteria = [];
$nilai = [];

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
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Penilaian Supplier</h3>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Nilai harus angka dan tidak boleh kosong</div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Data berhasil disimpan</div>
    <?php endif; ?>

    <form method="GET" class="mb-3">
        <select name="proyek" class="form-control" onchange="this.form.submit()">
            <option value="">-- Pilih Proyek --</option>
            <?php while($p = mysqli_fetch_assoc($proyek)): ?>
                <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek==$p['id_proyek']?'selected':''; ?>>
                    <?= $p['nama_proyek']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($id_proyek): ?>

        <?php if (mysqli_num_rows($alternatif) == 0): ?>
            <div class="alert alert-warning">Belum ada alternatif</div>
        <?php elseif (mysqli_num_rows($kriteria) == 0): ?>
            <div class="alert alert-warning">Belum ada kriteria</div>
        <?php else: ?>

        <form method="POST" action="simpan.php">

            <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">

            <table class="table table-bordered">

                <tr>
                    <th>Supplier</th>
                    <?php while($k = mysqli_fetch_assoc($kriteria)): ?>
                        <th><?= $k['nama_kriteria']; ?></th>
                    <?php endwhile; ?>
                </tr>

                <?php
                mysqli_data_seek($kriteria, 0);
                while($a = mysqli_fetch_assoc($alternatif)):
                ?>
                <tr>
                    <td><?= $a['nama_supplier']; ?></td>

                    <?php while($k = mysqli_fetch_assoc($kriteria)): 
                        $val = $nilai[$a['id_alternatif']][$k['id_kriteria']] ?? '';
                    ?>
                        <td>
                            <input type="number" step="any"
                            name="nilai[<?= $a['id_alternatif']; ?>][<?= $k['id_kriteria']; ?>]"
                            value="<?= $val; ?>"
                            class="form-control"
                            required>
                        </td>
                    <?php endwhile; ?>

                </tr>
                <?php 
                mysqli_data_seek($kriteria, 0);
                endwhile; 
                ?>

            </table>

            <button type="submit" class="btn btn-success">Simpan</button>

        </form>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>