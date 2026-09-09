<?php require_once __DIR__ . '/../config/config.php'; ?>
</div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/custom.js"></script>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($flash):
?>
<script>
window.addEventListener("load", function () {
    showToast(<?= json_encode($flash['message']); ?>, <?= json_encode($flash['type']); ?>);
});
</script>
<?php endif; ?>

<!-- MODAL KONFIRMASI (hapus) -->
<div class="modal fade" id="catConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px;">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div class="flash-cat">🐱</div>
                <h5 class="flash-title">Konfirmasi</h5>
                <p class="text-muted mb-3" id="catConfirmMsg"></p>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger ms-2" id="catConfirmYes"><i class="bi bi-check-circle"></i> Ya</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>