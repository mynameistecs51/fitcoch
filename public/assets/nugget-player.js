(function () {
    const config = window.FitCochNugget;

    if (!config || !config.progressUrl) {
        return;
    }

    let lastSent = config.initialProgress || 0;
    let heartbeatTimer = null;

    function updateUi(percentage, status) {
        const bar = document.getElementById('nugget-progress-bar');
        const text = document.getElementById('nugget-progress-text');
        const statusEl = document.getElementById('nugget-progress-status');

        if (bar) {
            bar.style.width = percentage + '%';
        }

        if (text) {
            text.textContent = percentage + '%';
        }

        if (statusEl) {
            statusEl.textContent = status === 'completed'
                ? config.labels.completed
                : config.labels.inProgress;
        }
    }

    function sendProgress(percentage) {
        const rounded = Math.max(0, Math.min(100, Math.round(percentage)));

        if (rounded <= lastSent && rounded < 90) {
            return;
        }

        lastSent = rounded;

        fetch(config.progressUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ progress_percentage: rounded }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success || !payload.data) {
                    return;
                }

                updateUi(payload.data.progress_percentage, payload.data.status);
            })
            .catch(function () {
                // Ignore transient heartbeat failures.
            });
    }

    function scheduleHeartbeat(percentage) {
        sendProgress(percentage);
        clearInterval(heartbeatTimer);
        heartbeatTimer = window.setInterval(function () {
            sendProgress(percentage);
        }, 10000);
    }

    const video = document.getElementById('nugget-video-player');

    if (video) {
        video.addEventListener('timeupdate', function () {
            if (!video.duration || Number.isNaN(video.duration)) {
                return;
            }

            const percentage = (video.currentTime / video.duration) * 100;
            scheduleHeartbeat(percentage);
        });

        video.addEventListener('ended', function () {
            sendProgress(100);
        });

        return;
    }

    if (document.getElementById('nugget-youtube-player')) {
        let simulated = config.initialProgress || 5;
        scheduleHeartbeat(simulated);

        window.setInterval(function () {
            simulated = Math.min(95, simulated + 2);
            scheduleHeartbeat(simulated);
        }, 15000);
    }
})();
