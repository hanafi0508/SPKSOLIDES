<div class="col-md-2 bg-dark text-white vh-100 p-3">

    <ul class="nav flex-column">

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="/pages/dashboard.php">
                Dashboard
            </a>
        </li>

        <?php if ($_SESSION['level'] == 'admin'): ?>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="/pages/kriteria/index.php">
                    Kriteria
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/supplier/index.php">
                    Supplier
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/proyek/index.php">
                    Proyek
                </a>
            </li>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="/pages/alternatif/index.php">
                    Alternatif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/penilaian/index.php">
                    Penilaian
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/ahp/input_perbandingan.php">
                    AHP
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/fahp/input_perbandingan.php">
                    F-AHP
                </a>
            </li>

        <?php endif; ?>

        <?php if ($_SESSION['level'] == 'pimpinan'): ?>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="/pages/ranking/index.php">
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="/pages/laporan/index.php">
                    Laporan
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>
