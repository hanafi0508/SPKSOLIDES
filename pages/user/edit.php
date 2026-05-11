<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['submit'])) {

    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $level = $_POST['level'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        mysqli_query($conn, "UPDATE users SET 
            nama_user='$nama',
            username='$username',
            password='$password',
            level='$level'
            WHERE id_user='$id'
        ");
    } else {
        mysqli_query($conn, "UPDATE users SET 
            nama_user='$nama',
            username='$username',
            level='$level'
            WHERE id_user='$id'
        ");
    }

    header("Location: index.php");
    exit;
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Edit User</h3>

    <form method="POST">

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= $row['nama_user']; ?>">
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= $row['username']; ?>">
        </div>

        <div class="mb-3">
            <label>Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Level</label>
            <select name="level" class="form-control">
                <option value="admin" <?= $row['level']=='admin'?'selected':''; ?>>Admin</option>
                <option value="pimpinan" <?= $row['level']=='pimpinan'?'selected':''; ?>>Pimpinan</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Update</button>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
