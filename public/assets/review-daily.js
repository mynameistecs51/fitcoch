(function () {
    const scene = document.getElementById('review-card');
    const inner = document.getElementById('flashcard-inner');
    const revealBtn = document.getElementById('reveal-btn');
    const form = document.getElementById('review-rating-form');
    const wrap = document.getElementById('review-card-wrap');

    if (!scene || !inner) {
        return;
    }

    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (sessionStorage.getItem('fitcoch-review-enter') === '1') {
        sessionStorage.removeItem('fitcoch-review-enter');
        if (!reducedMotion && wrap) {
            wrap.classList.add('is-entering');
        }
    }

    revealBtn?.addEventListener('click', function () {
        if (reducedMotion) {
            inner.classList.add('is-flipped');
            revealBtn.setAttribute('aria-expanded', 'true');
            return;
        }

        inner.classList.add('is-flipped');
        revealBtn.setAttribute('aria-expanded', 'true');
        revealBtn.disabled = true;
    });

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const apiUrl = form.dataset.apiUrl || '';
    const redirectUrl = form.dataset.redirectUrl || window.location.pathname;

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const submitter = event.submitter;
        const rating = submitter instanceof HTMLButtonElement ? submitter.value : null;

        if (!rating || !apiUrl || form.dataset.submitting === '1') {
            return;
        }

        form.dataset.submitting = '1';
        form.querySelectorAll('.review-rating-btn').forEach(function (btn) {
            btn.classList.add('is-submitting');
        });

        function finishRedirect(xpAwarded) {
            if (xpAwarded > 0 && !reducedMotion) {
                showXpToast(xpAwarded);
            }

            sessionStorage.setItem('fitcoch-review-enter', '1');

            window.setTimeout(function () {
                window.location.href = redirectUrl;
            }, reducedMotion ? 0 : 320);
        }

        function slideThen(callback) {
            if (reducedMotion || !wrap) {
                callback();
                return;
            }

            wrap.classList.add('is-sliding-out');
            window.setTimeout(callback, 360);
        }

        slideThen(function () {
            fetch(apiUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ rating: parseInt(rating, 10) }),
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        throw new Error(result.payload?.message || 'Review failed');
                    }

                    const xpAwarded = result.payload?.data?.xp_awarded || 0;
                    finishRedirect(xpAwarded);
                })
                .catch(function () {
                    form.dataset.submitting = '0';
                    form.querySelectorAll('.review-rating-btn').forEach(function (btn) {
                        btn.classList.remove('is-submitting');
                    });

                    if (wrap) {
                        wrap.classList.remove('is-sliding-out');
                    }

                    form.submit();
                });
        });
    });

    function showXpToast(xp) {
        const toast = document.createElement('div');
        toast.className = 'review-xp-toast';
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<i class="fa-solid fa-star"></i><span>+' + xp + ' XP</span>';
        document.body.appendChild(toast);

        window.setTimeout(function () {
            toast.remove();
        }, 2600);
    }
})();
