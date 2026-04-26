<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id_proyek = $_GET['proyek'];

$supplier = mysqli_query($conn, "SELECT * FROM supplier WHERE status='aktif'");

$error = '';

if (isset($_POST['submit'])) {

    $id_supplier = $_POST['supplier'];

    $cek = mysqli_query($conn, "
        SELECT * FROM alternatif 
        WHERE id_proyek='$id_proyek' AND id_supplier='$id_supplier'
    ");

    if (mysqli_num_rows($cek) > 0) {
        $error = "Supplier sudah ada di proyek ini";
    } else {

        mysqli_query($conn, "
            INSERT INTO alternatif (id_proyek, id_supplier)
            VALUES ('$id_proyek','$id_supplier')
        ");

        header("Location: index.php?proyek=$id_proyek");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah Alternatif</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Pilih Supplier</label>
            <select name="supplier" class="form-control" required>
                <option value="">-- Pilih Supplier --</option>
                <?php while($s = mysqli_fetch_assoc($supplier)): ?>
                    <option value="<?= $s['id_supplier']; ?>">
                        <?= $s['nama_supplier']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php?proyek=<?= $id_proyek; ?>" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
