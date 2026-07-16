(function () {
    'use strict';

    function animateStat(el) {
        const target = parseInt(el.dataset.target || '0', 10);
        if (target <= 0) {
            el.textContent = '0';
            return;
        }

        const duration = 1200;
        const start = performance.now();

        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }

        requestAnimationFrame(tick);
    }

    document.querySelectorAll('.landing-stat-value').forEach(animateStat);

    const grid = document.getElementById('landing-course-grid');
    const pills = document.querySelectorAll('.landing-category-pill');

    if (!grid || pills.length === 0) {
        return;
    }

    const cards = grid.querySelectorAll('.landing-course-card');

    pills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            const keywords = (pill.dataset.categoryKeywords || '').toLowerCase().split(/\s+/).filter(Boolean);
            const isActive = pill.classList.contains('is-active');

            pills.forEach(function (p) {
                p.classList.remove('is-active');
            });

            if (isActive) {
                cards.forEach(function (card) {
                    card.classList.remove('is-hidden');
                });
                return;
            }

            pill.classList.add('is-active');

            cards.forEach(function (card) {
                const haystack = (
                    (card.dataset.courseTitle || '') + ' ' + (card.dataset.courseDescription || '')
                ).toLowerCase();
                const match = keywords.some(function (word) {
                    return word !== '' && haystack.includes(word);
                });
                card.classList.toggle('is-hidden', !match);
            });
        });
    });
})();
