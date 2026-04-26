<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">

        <span class="navbar-brand">SPK Supplier</span>

        <div class="d-flex">
            <span class="text-white me-3">
                <?= $_SESSION['nama_user']; ?>
            </span>

            <a href="../auth/logout.php" class="btn btn-danger btn-sm">
                Logout
            </a>
        </div>

    </div>
</nav>