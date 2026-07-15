(function () {
    const room = document.getElementById('live-room');

    if (!room) {
        return;
    }

    const joinBtn = document.getElementById('live-join-btn');
    const leaveBtn = document.getElementById('live-leave-btn');
    const statusEl = document.getElementById('live-connection-status');
    const videoEl = document.getElementById('live-local-video');
    const placeholderEl = document.getElementById('live-video-placeholder');
    const participantsBody = document.getElementById('live-participants-body');
    const onlineCountEl = document.getElementById('live-online-count');
    const statusBadge = document.getElementById('live-status-badge');
    const activateBtn = document.getElementById('live-activate-btn');
    const completeBtn = document.getElementById('live-complete-btn');
    const toggleCameraBtn = document.getElementById('live-toggle-camera');
    const toggleMicBtn = document.getElementById('live-toggle-mic');

    const labels = JSON.parse(room.dataset.labels || '{}');
    const isHost = room.dataset.isHost === '1';
    let localStream = null;
    let joined = false;
    let pollTimer = null;
    let cameraEnabled = true;
    let micEnabled = true;

    function setStatus(message) {
        if (statusEl) {
            statusEl.textContent = message;
        }
    }

    async function postAction(url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ csrf_token: room.dataset.csrf || '' }),
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message || 'Live session request failed.');
        }

        return payload.data;
    }

    async function getJson(url) {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message || 'Request failed.');
        }

        return payload.data;
    }

    async function startLocalPreview() {
        if (!navigator.mediaDevices || !videoEl) {
            return;
        }

        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            videoEl.srcObject = localStream;
            if (placeholderEl) {
                placeholderEl.hidden = true;
            }
        } catch (error) {
            setStatus('Camera/microphone unavailable.');
        }
    }

    function stopLocalPreview() {
        if (localStream) {
            localStream.getTracks().forEach(function (track) {
                track.stop();
            });
            localStream = null;
        }

        if (videoEl) {
            videoEl.srcObject = null;
        }

        if (placeholderEl) {
            placeholderEl.hidden = false;
        }
    }

    function ticketLabel(status) {
        return labels['ticket_' + status] || status;
    }

    function presenceLabel(presence) {
        return labels['presence_' + presence] || presence;
    }

    function statusLabel(status) {
        return labels['status_' + status] || status;
    }

    function renderParticipants(participants, onlineCount) {
        if (!participantsBody) {
            return;
        }

        participantsBody.innerHTML = '';

        participants.forEach(function (row) {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="px-3 py-2 text-sm"><p class="font-medium text-slate-900 dark:text-slate-200">' +
                escapeHtml(row.first_name + ' ' + row.last_name) +
                '</p><p class="text-xs text-slate-400">' + escapeHtml(row.email) + '</p></td>' +
                '<td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">' + escapeHtml(ticketLabel(row.ticket_status)) + '</td>' +
                '<td class="px-3 py-2 text-xs font-semibold text-brand-600 dark:text-brand-accent">' +
                escapeHtml(presenceLabel(row.presence)) + '</td>';
            participantsBody.appendChild(tr);
        });

        if (onlineCountEl) {
            onlineCountEl.textContent = onlineCount + ' ' + (labels.online || 'online');
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    async function refreshParticipants() {
        if (!isHost || !room.dataset.participantsUrl) {
            return;
        }

        try {
            const data = await getJson(room.dataset.participantsUrl);
            renderParticipants(data.participants || [], data.online_count || 0);
        } catch (error) {
            // Ignore polling errors silently.
        }
    }

    function startPolling() {
        if (!isHost) {
            return;
        }

        refreshParticipants();
        pollTimer = window.setInterval(refreshParticipants, 5000);
    }

    function stopPolling() {
        if (pollTimer) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function updateSessionStatus(status) {
        room.dataset.sessionStatus = status;

        if (statusBadge) {
            statusBadge.textContent = statusLabel(status);
        }

        if (activateBtn) {
            activateBtn.disabled = status !== 'scheduled';
            activateBtn.classList.toggle('opacity-50', status !== 'scheduled');
            activateBtn.classList.toggle('cursor-not-allowed', status !== 'scheduled');
        }

        if (completeBtn) {
            completeBtn.disabled = status !== 'active';
            completeBtn.classList.toggle('opacity-50', status !== 'active');
            completeBtn.classList.toggle('cursor-not-allowed', status !== 'active');
        }
    }

    if (isHost) {
        startPolling();
    }

    if (joinBtn) {
        joinBtn.addEventListener('click', async function () {
            joinBtn.disabled = true;

            try {
                await postAction(room.dataset.joinUrl || '');
                joined = true;
                joinBtn.hidden = true;
                if (leaveBtn) {
                    leaveBtn.hidden = false;
                }
                setStatus(labels.connected || 'Connected');
                await startLocalPreview();
                startPolling();
            } catch (error) {
                setStatus(error.message || 'Unable to join live session.');
                joinBtn.disabled = false;
            }
        });
    }

    if (leaveBtn) {
        leaveBtn.addEventListener('click', async function () {
            leaveBtn.disabled = true;

            try {
                await postAction(room.dataset.leaveUrl || '');
            } catch (error) {
                setStatus(error.message || 'Unable to leave live session.');
            }

            stopLocalPreview();
            stopPolling();
            joined = false;
            joinBtn.hidden = false;
            joinBtn.disabled = false;
            leaveBtn.hidden = true;
            leaveBtn.disabled = false;
            setStatus(labels.disconnected || 'Disconnected');
        });
    }

    if (activateBtn) {
        activateBtn.addEventListener('click', async function () {
            activateBtn.disabled = true;

            try {
                const data = await postAction(room.dataset.activateUrl || '');
                updateSessionStatus(data.session?.status || 'active');
                setStatus(labels.activate_success || 'Broadcast started.');
            } catch (error) {
                setStatus(error.message);
            }

            activateBtn.disabled = room.dataset.sessionStatus !== 'scheduled';
        });
    }

    if (completeBtn) {
        completeBtn.addEventListener('click', async function () {
            if (!window.confirm(labels.confirm_end || 'End this live session?')) {
                return;
            }

            completeBtn.disabled = true;

            try {
                const data = await postAction(room.dataset.completeUrl || '');
                updateSessionStatus(data.session?.status || 'completed');
                setStatus(labels.complete_success || 'Broadcast ended.');
            } catch (error) {
                setStatus(error.message);
            }

            completeBtn.disabled = room.dataset.sessionStatus !== 'active';
        });
    }

    if (toggleCameraBtn) {
        toggleCameraBtn.addEventListener('click', function () {
            if (!localStream) {
                return;
            }

            cameraEnabled = !cameraEnabled;
            localStream.getVideoTracks().forEach(function (track) {
                track.enabled = cameraEnabled;
            });
            toggleCameraBtn.classList.toggle('bg-red-600', !cameraEnabled);
        });
    }

    if (toggleMicBtn) {
        toggleMicBtn.addEventListener('click', function () {
            if (!localStream) {
                return;
            }

            micEnabled = !micEnabled;
            localStream.getAudioTracks().forEach(function (track) {
                track.enabled = micEnabled;
            });
            toggleMicBtn.classList.toggle('bg-red-600', !micEnabled);
        });
    }

    window.addEventListener('beforeunload', function () {
        if (!joined) {
            return;
        }

        navigator.sendBeacon(
            room.dataset.leaveUrl || '',
            new Blob([JSON.stringify({ csrf_token: room.dataset.csrf || '' })], { type: 'application/json' })
        );
    });
})();
