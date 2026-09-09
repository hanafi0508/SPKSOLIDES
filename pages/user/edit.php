<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = db_one($conn, 'SELECT * FROM users WHERE id_user = ?', 'i', [$id]);

if (!$row) {
    header('Location: index.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {

    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $level = $_POST['level'];
    $password = $_POST['password'] ?? '';

    if (db_one($conn, 'SELECT id_user FROM users WHERE username = ? AND id_user != ?', 'si', [$username, $id])) {
        $error = 'Username sudah digunakan';
    } elseif (!in_array($level, ['admin', 'pimpinan'], true)) {
        $error = 'Level user tidak valid';
    } elseif ($password !== '') {
        $hash = hash_user_password($password);
        db_exec($conn, 'UPDATE users SET nama_user = ?, username = ?, password = ?, level = ? WHERE id_user = ?', 'ssssi', [$nama, $username, $hash, $level, $id]);
        header('Location: index.php');
        exit;
    } else {
        db_exec($conn, 'UPDATE users SET nama_user = ?, username = ?, level = ? WHERE id_user = ?', 'sssi', [$nama, $username, $level, $id]);
        header('Location: index.php');
        exit;
    }
}

layout_top();
?>
<h3>Edit User</h3>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= h($error); ?></div>
<?php endif; ?>

<form method="POST">
    <?php csrf_field(); ?>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" value="<?= h($row['nama_user']); ?>">
    </div>

    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="<?= h($row['username']); ?>">
    </div>

    <div class="mb-3">
        <label>Password (kosongkan jika tidak diubah)</label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
        <label>Level</label>
        <select name="level" class="form-control">
            <option value="admin" <?= $row['level'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="pimpinan" <?= $row['level'] == 'pimpinan' ? 'selected' : ''; ?>>Pimpinan</option>
        </select>
    </div>

    <button name="submit" class="btn btn-success">Update</button>
</form>
<?php layout_bottom(); ?>