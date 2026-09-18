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

    // ===== Isi form modal untuk Tambah / Edit =====

    function setModalTitle(modal, label, action) {
        const title = modal.querySelector('.modal-title');
        const targetLabel = modal.getAttribute('data-add-label') || label;
        if (title) title.textContent = action + ' ' + targetLabel;
    }

    function resetForm(modal) {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const idField = form.querySelector('[name="id"]');
            if (idField) idField.value = '';
        }
        setModalTitle(modal, '', 'Tambah');
    }

    function fillEditForm(modal, data) {
        const form = modal.querySelector('form');
        if (!form) return;

        form.reset();
        const idField = form.querySelector('[name="id"]');
        if (idField) idField.value = data.id || '';

        form.querySelectorAll('[name]').forEach(function (field) {
            const name = field.getAttribute('name');
            const value = data[name];
            if (name === 'id' || typeof value === 'undefined') return;

            if (field.tagName === 'SELECT') {
                const option = field.querySelector('option[value="' + value + '"]');
                if (option) option.selected = true;
            } else {
                field.value = value;
            }
        });

        setModalTitle(modal, '', 'Edit');
    }

    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = document.querySelector(btn.getAttribute('data-bs-target'));
            if (!modal) return;

            if (btn.classList.contains('btn-add')) {
                resetForm(modal);
            } else if (btn.classList.contains('btn-edit')) {
                fillEditForm(modal, btn.dataset);
            }
        });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            resetForm(modal);
        });
    });
});