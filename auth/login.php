<?php
require_once '../config/session.php';

if (isset($_SESSION['id_user'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login - SPK Supplier</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-4 mx-auto">
        <div class="card shadow p-4">

            <h4 class="text-center mb-3">Login</h4>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Username atau password salah!
                </div>
            <?php endif; ?>

            <form method="POST" action="proses_login.php">

                <div class="mb-3">
                    <label>Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        required
                    >
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Login
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
