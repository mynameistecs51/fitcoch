(function () {
    const overlayId = 'form-progress-overlay';
    let indeterminateTimer = null;
    let overlay = null;

    function getOverlay() {
        if (overlay) {
            return overlay;
        }

        overlay = document.createElement('div');
        overlay.id = overlayId;
        overlay.className = 'form-progress-overlay hidden';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-live', 'polite');
        overlay.innerHTML = [
            '<div class="form-progress-card">',
            '  <div class="form-progress-icon-wrap">',
            '    <div class="form-progress-icon-ring"></div>',
            '    <i class="fa-solid fa-cloud-arrow-up form-progress-icon" aria-hidden="true"></i>',
            '  </div>',
            '  <p class="form-progress-label"></p>',
            '  <p class="form-progress-status"></p>',
            '  <div class="form-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">',
            '    <div class="form-progress-fill"></div>',
            '    <div class="form-progress-shimmer"></div>',
            '  </div>',
            '  <p class="form-progress-percent">0%</p>',
            '</div>',
        ].join('');

        document.body.appendChild(overlay);
        return overlay;
    }

    function setIcon(mode) {
        const icon = getOverlay().querySelector('.form-progress-icon');
        if (!icon) {
            return;
        }

        icon.className = 'fa-solid form-progress-icon ' + (mode === 'upload' ? 'fa-cloud-arrow-up' : 'fa-spinner form-progress-spin');
        icon.setAttribute('aria-hidden', 'true');
    }

    function showProgress(label, mode, statusText) {
        const root = getOverlay();
        const labelEl = root.querySelector('.form-progress-label');
        const statusEl = root.querySelector('.form-progress-status');
        const fillEl = root.querySelector('.form-progress-fill');
        const percentEl = root.querySelector('.form-progress-percent');
        const trackEl = root.querySelector('.form-progress-track');

        clearInterval(indeterminateTimer);
        indeterminateTimer = null;
        setIcon(mode);
        labelEl.textContent = label;
        statusEl.textContent = statusText;
        fillEl.style.width = '0%';
        fillEl.classList.remove('is-indeterminate');
        percentEl.textContent = mode === 'upload' ? '0%' : '';
        percentEl.hidden = mode !== 'upload';
        trackEl.setAttribute('aria-valuenow', '0');
        root.classList.remove('hidden');
        document.body.classList.add('form-progress-active');
    }

    function updateProgress(percent, statusText) {
        const root = getOverlay();
        const fillEl = root.querySelector('.form-progress-fill');
        const percentEl = root.querySelector('.form-progress-percent');
        const statusEl = root.querySelector('.form-progress-status');
        const trackEl = root.querySelector('.form-progress-track');
        const clamped = Math.max(0, Math.min(100, Math.round(percent)));

        fillEl.style.width = clamped + '%';
        fillEl.classList.remove('is-indeterminate');
        percentEl.textContent = clamped + '%';
        percentEl.hidden = false;
        trackEl.setAttribute('aria-valuenow', String(clamped));

        if (statusText) {
            statusEl.textContent = statusText;
        }
    }

    function startIndeterminate(label, statusText) {
        const root = getOverlay();
        const fillEl = root.querySelector('.form-progress-fill');
        const percentEl = root.querySelector('.form-progress-percent');
        const statusEl = root.querySelector('.form-progress-status');
        const trackEl = root.querySelector('.form-progress-track');

        clearInterval(indeterminateTimer);
        setIcon('submit');
        root.querySelector('.form-progress-label').textContent = label;
        statusEl.textContent = statusText;
        fillEl.classList.remove('is-indeterminate');
        fillEl.style.width = '12%';
        percentEl.hidden = true;
        trackEl.setAttribute('aria-valuenow', '0');

        let value = 12;
        indeterminateTimer = window.setInterval(function () {
            value = Math.min(92, value + Math.random() * 6);
            fillEl.style.width = value + '%';
            trackEl.setAttribute('aria-valuenow', String(Math.round(value)));
        }, 320);
    }

    function hideProgress() {
        clearInterval(indeterminateTimer);
        indeterminateTimer = null;

        if (!overlay) {
            return;
        }

        overlay.classList.add('hidden');
        document.body.classList.remove('form-progress-active');
    }

    function formHasSelectedFile(form) {
        return Array.from(form.querySelectorAll('input[type="file"]')).some(function (input) {
            return input.files && input.files.length > 0;
        });
    }

    function shouldUseUploadMode(form) {
        const mode = form.dataset.progressMode || 'auto';

        if (mode === 'submit') {
            return false;
        }

        if (mode === 'upload') {
            return formHasSelectedFile(form);
        }

        const enctype = (form.getAttribute('enctype') || '').toLowerCase();
        return enctype.includes('multipart') && formHasSelectedFile(form);
    }

    function getLabels(form, useUpload) {
        const processingText = form.dataset.progressProcessing || form.dataset.progressLabel || 'Please wait...';
        const label = useUpload && form.dataset.progressUploadLabel
            ? form.dataset.progressUploadLabel
            : (form.dataset.progressLabel || 'Loading...');

        return { label: label, processingText: processingText };
    }

    function disableForm(form, disabled) {
        form.querySelectorAll('button, input, select, textarea').forEach(function (element) {
            if (element.type === 'hidden') {
                return;
            }
            element.disabled = disabled;
        });
    }

    function submitWithUploadProgress(form, label, processingText) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData(form);
        const actionUrl = new URL(form.action, window.location.origin);

        showProgress(label, 'upload', label);
        disableForm(form, true);

        xhr.open(form.method || 'POST', form.action, true);
        xhr.withCredentials = true;
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function (event) {
            if (!event.lengthComputable) {
                return;
            }

            const percent = (event.loaded / event.total) * 100;
            updateProgress(percent, label);

            if (percent >= 100) {
                updateProgress(100, processingText);
            }
        });

        xhr.addEventListener('load', function () {
            disableForm(form, false);
            form.dataset.progressSubmitting = '0';

            if (xhr.status >= 200 && xhr.status < 400) {
                const responseUrl = new URL(xhr.responseURL || form.action, window.location.origin);
                const samePath = responseUrl.pathname === actionUrl.pathname && responseUrl.search === actionUrl.search;

                if (!samePath) {
                    window.location.href = xhr.responseURL;
                    return;
                }

                document.open();
                document.write(xhr.responseText);
                document.close();
                hideProgress();
                return;
            }

            hideProgress();
        });

        xhr.addEventListener('error', function () {
            disableForm(form, false);
            form.dataset.progressSubmitting = '0';
            hideProgress();
        });

        xhr.send(formData);
    }

    function handleSubmit(event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-progress')) {
            return;
        }

        if (form.dataset.progressSubmitting === '1') {
            event.preventDefault();
            return;
        }

        const useUpload = shouldUseUploadMode(form);
        const labels = getLabels(form, useUpload);

        if (useUpload) {
            event.preventDefault();
            form.dataset.progressSubmitting = '1';
            submitWithUploadProgress(form, labels.label, labels.processingText);
            return;
        }

        form.dataset.progressSubmitting = '1';
        startIndeterminate(labels.label, labels.processingText);
        disableForm(form, true);
    }

    document.addEventListener('submit', handleSubmit, true);

    window.FitCochFormProgress = {
        hide: hideProgress,
    };
})();
