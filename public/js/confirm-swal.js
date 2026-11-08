/**
 * SweetAlert confirmation for any form that carries data-confirm.
 *
 *   <form method="POST" ... data-confirm="Remove this?" data-confirm-title="Remove"
 *         data-confirm-yes="Remove" data-confirm-no="Cancel">
 *
 * Replaces the browser's native confirm() dialog.
 */
(function () {
    'use strict';

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') return;

        e.preventDefault();

        // Without SweetAlert on the page, fall back to the native dialog.
        if (!window.Swal) {
            if (window.confirm(form.dataset.confirm)) { form.dataset.confirmed = '1'; form.submit(); }
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: form.dataset.confirmTitle || '',
            text: form.dataset.confirm,
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmYes || 'OK',
            cancelButtonText: form.dataset.confirmNo || 'Cancel',
            confirmButtonColor: '#ff595e',
            cancelButtonColor: '#8b949d',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });
})();
