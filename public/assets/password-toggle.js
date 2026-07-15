(function () {
    document.querySelectorAll('.password-toggle-wrap').forEach(function (wrap) {
        const input = wrap.querySelector('input[type="password"], input[type="text"][data-password-input]');

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const button = wrap.querySelector('.password-toggle-btn');

        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        const icon = button.querySelector('i');
        const showLabel = button.dataset.labelShow || 'Show password';
        const hideLabel = button.dataset.labelHide || 'Hide password';

        button.addEventListener('click', function () {
            const revealing = input.type === 'password';
            input.type = revealing ? 'text' : 'password';

            if (revealing) {
                input.setAttribute('data-password-input', '1');
            } else {
                input.removeAttribute('data-password-input');
            }

            if (icon) {
                icon.classList.toggle('fa-eye', !revealing);
                icon.classList.toggle('fa-eye-slash', revealing);
            }

            button.setAttribute('aria-label', revealing ? hideLabel : showLabel);
            button.setAttribute('aria-pressed', revealing ? 'true' : 'false');
        });
    });
})();
