(function () {
    if (!window.FitCochQuizCelebration || window.FitCochQuizCelebration.passed !== true) {
        return;
    }

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    function runWithConfetti() {
        if (typeof window.confetti !== 'function') {
            return;
        }

        const brandColors = ['#22c55e', '#a3e635', '#16a34a', '#fbbf24', '#38bdf8', '#f472b6', '#ffffff'];
        const duration = 3200;
        const end = Date.now() + duration;

        function burst(originX, originY) {
            window.confetti({
                particleCount: 55,
                spread: 72,
                startVelocity: 42,
                gravity: 0.9,
                ticks: 180,
                origin: { x: originX, y: originY },
                colors: brandColors,
                zIndex: 10000,
            });
        }

        function firework() {
            burst(0.5, 0.55);
            window.setTimeout(function () {
                burst(0.2, 0.65);
                burst(0.8, 0.65);
            }, 180);
        }

        firework();

        const interval = window.setInterval(function () {
            if (Date.now() > end) {
                window.clearInterval(interval);
                return;
            }

            firework();
        }, 520);
    }

    function loadConfetti(callback) {
        if (typeof window.confetti === 'function') {
            callback();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js';
        script.async = true;
        script.onload = callback;
        document.head.appendChild(script);
    }

    function highlightResultCard() {
        const card = document.querySelector('[data-quiz-passed-result]');

        if (card) {
            card.classList.add('quiz-passed-celebrate');
        }
    }

    loadConfetti(function () {
        highlightResultCard();
        window.setTimeout(runWithConfetti, 120);
    });
})();
