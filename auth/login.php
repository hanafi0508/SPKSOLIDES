<?php
require_once '../config/session.php';
require_once '../config/config.php';
require_once '../functions/auth_function.php';

if (isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>SOLIDES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/custom.css" rel="stylesheet">
</head>
<body class="login-shell">


<div class="container">
    <div class="col-md-5 col-lg-4 mx-auto">
        <div class="card login-card shadow p-4">

            <div class="text-center mb-3">
                <div class="brand-logo mx-auto mb-2">SOLIDES</div>
                <h4 class="login-title fw-bold">SOLIDES</h4>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Username atau password salah!
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash'])): ?>
                <?php $fh = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                <div class="alert alert-<?= htmlspecialchars($fh['type'] == 'danger' ? 'danger' : 'success'); ?>">
                    <?= htmlspecialchars($fh['message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/proses_login.php">

                <?php csrf_field(); ?>

                <div class="mb-3">
                    <label>Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Masuk
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
