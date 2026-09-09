// Project scripts
document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    const currentUrl = window.location.pathname + window.location.search;

    document.querySelectorAll('.app-sidebar .nav-link[href]').forEach(function (link) {
        const linkUrl = new URL(link.href, window.location.origin);
        const linkPath = linkUrl.pathname;
        const linkFullPath = linkUrl.pathname + linkUrl.search;

        if (currentUrl === linkFullPath || (linkUrl.search === '' && currentPath === linkPath)) {
            link.classList.add('active');
            const collapse = link.closest('.collapse');

            if (collapse) {
                collapse.classList.add('show');
                const trigger = document.querySelector('[href="#' + collapse.id + '"]');

                if (trigger) {
                    trigger.classList.add('active');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
});

// Konfirmasi aksi (hapus) dengan modal kucing
function catConfirm(message, onYes) {
    var el = document.getElementById('catConfirmModal');
    document.getElementById('catConfirmMsg').textContent = message;
    var yes = document.getElementById('catConfirmYes');
    var modal = bootstrap.Modal.getOrCreateInstance(el);
    yes.disabled = false;
    yes.onclick = function () {
        yes.disabled = true;
        if (onYes) onYes();
        modal.hide();
    };
    modal.show();
}

document.addEventListener('click', function (e) {
    var link = e.target.closest('a[data-confirm]');
    if (link) {
        e.preventDefault();
        catConfirm(link.getAttribute('data-confirm'), function () {
            window.location.href = link.href;
        });
    }
});

// Toast global kanan-atas — dipakai untuk flash server & respons AJAX
function showToast(message, type) {
    var container = document.querySelector('.toast-container');
    if (!container) return;

    var isError = type === 'error';
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-' + (isError ? 'danger' : 'success') + ' border-0';
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');

    var body = document.createElement('div');
    body.className = 'toast-body';
    body.innerHTML = '<span class="flash-cat-sm">🐱</span><strong>' + (isError ? 'Gagal!' : 'Berhasil!') + '</strong> ';
    body.appendChild(document.createTextNode(message));

    var wrap = document.createElement('div');
    wrap.className = 'd-flex';
    wrap.appendChild(body);
    el.appendChild(wrap);

    container.appendChild(el);
    var toast = new bootstrap.Toast(el, { delay: 3000, autohide: true });
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    toast.show();
}
