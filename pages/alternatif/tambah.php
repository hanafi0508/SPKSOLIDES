<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id_proyek = isset($_GET['proyek']) ? (int) $_GET['proyek'] : 0;

$supplier = mysqli_query($conn, "SELECT * FROM supplier WHERE status='aktif' ORDER BY tipe_supplier ASC, nama_supplier ASC");

$error = '';

if (isset($_POST['submit'])) {

    $id_supplier = (int) $_POST['supplier'];

    $stmtCek = mysqli_prepare($conn, "
        SELECT id_alternatif FROM alternatif
        WHERE id_proyek = ? AND id_supplier = ?
    ");
    mysqli_stmt_bind_param($stmtCek, "ii", $id_proyek, $id_supplier);
    mysqli_stmt_execute($stmtCek);
    $cek = mysqli_stmt_get_result($stmtCek);

    if (mysqli_num_rows($cek) > 0) {
        $error = "Supplier sudah ada di proyek ini";
    } else {
        $stmtInsert = mysqli_prepare($conn, "
            INSERT INTO alternatif (id_proyek, id_supplier)
            VALUES (?, ?)
        ");
        mysqli_stmt_bind_param($stmtInsert, "ii", $id_proyek, $id_supplier);
        mysqli_stmt_execute($stmtInsert);

        header("Location: index.php?proyek=$id_proyek");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah Supplier ke Perhitungan</h3>

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
                        <?= htmlspecialchars($s['nama_supplier']); ?> - <?= ucfirst(htmlspecialchars($s['tipe_supplier'] ?? 'barang')); ?> - <?= htmlspecialchars($s['jenis_material']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php?proyek=<?= $id_proyek; ?>" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
