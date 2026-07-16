(function () {
    var board = document.getElementById('discussion-board');
    var feed = document.getElementById('discussion-messages');
    var form = document.getElementById('discussion-form');
    var alertBox = document.getElementById('discussion-alert');

    if (!board || !feed) {
        return;
    }

    var feedUrl = board.dataset.feedUrl || '';
    var postUrl = board.dataset.postUrl || '';
    var emptyText = board.dataset.emptyText || '';
    var successText = board.dataset.successText || '';
    var currentUserId = Number(board.dataset.currentUserId || 0);
    var pollIntervalMs = 4000;
    var pollTimer = null;
    var lastPostId = 0;
    var isSubmitting = false;

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(value) {
        var normalized = String(value).replace(' ', 'T');
        var date = new Date(normalized);

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        var pad = function (num) {
            return String(num).padStart(2, '0');
        };

        return pad(date.getDate()) + '/' + pad(date.getMonth() + 1) + '/' + date.getFullYear()
            + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function isNearBottom() {
        return feed.scrollHeight - feed.scrollTop - feed.clientHeight < 48;
    }

    function scrollFeedToLatest(force) {
        if (!feed) {
            return;
        }

        var shouldScroll = force || isNearBottom();

        if (!shouldScroll) {
            return;
        }

        feed.scrollTop = feed.scrollHeight;
    }

    function preservePageScroll(action) {
        var pageScrollY = window.scrollY;
        action();
        window.scrollTo(0, pageScrollY);
    }

    function showAlert(message, type) {
        if (!alertBox || !message) {
            return;
        }

        alertBox.textContent = message;
        alertBox.classList.remove('hidden', 'bg-brand-500/10', 'border-brand-500/20', 'text-brand-700', 'dark:text-brand-accent', 'bg-red-50', 'dark:bg-red-500/10', 'border-red-200', 'dark:border-red-500/20', 'text-red-700', 'dark:text-red-400', 'border');

        if (type === 'error') {
            alertBox.classList.add('bg-red-50', 'dark:bg-red-500/10', 'border', 'border-red-200', 'dark:border-red-500/20', 'text-red-700', 'dark:text-red-400');
        } else {
            alertBox.classList.add('bg-brand-500/10', 'border', 'border-brand-500/20', 'text-brand-700', 'dark:text-brand-accent');
        }

        window.setTimeout(function () {
            alertBox.classList.add('hidden');
        }, 3000);
    }

    function buildPostMarkup(post, isLatest) {
        var isOwn = Number(post.user_id) === currentUserId;
        var alignClass = isOwn ? 'justify-end' : 'justify-start';
        var columnClass = isOwn ? 'items-end' : 'items-start';
        var bubbleClass = isOwn
            ? 'rounded-2xl rounded-br-md bg-brand-500 text-slate-950 shadow-sm shadow-brand-500/20'
            : 'rounded-2xl rounded-bl-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-700';
        var timeClass = isOwn ? 'text-right' : 'text-left';
        var authorHtml = isOwn
            ? ''
            : '<span class="text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1 px-1 truncate max-w-full">'
                + escapeHtml(post.author_name || '')
                + '</span>';

        return ''
            + '<div class="flex ' + alignClass + '" data-post-id="' + escapeHtml(post.id) + '"'
            + (isLatest ? ' id="discussion-latest"' : '')
            + '>'
            + '<article class="max-w-[88%] min-w-0 ' + columnClass + ' flex flex-col">'
            + authorHtml
            + '<div class="' + bubbleClass + ' px-3 py-2">'
            + '<p class="text-sm leading-relaxed whitespace-pre-wrap break-words">' + escapeHtml(post.body) + '</p>'
            + '</div>'
            + '<time class="text-[10px] text-slate-400 mt-1 px-1 ' + timeClass + '" datetime="' + escapeHtml(post.created_at) + '">'
            + escapeHtml(formatDate(post.created_at))
            + '</time>'
            + '</article>'
            + '</div>';
    }

    function renderPosts(posts, forceScroll) {
        if (!Array.isArray(posts)) {
            return;
        }

        if (posts.length === 0) {
            feed.innerHTML = '<p id="discussion-empty" class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">'
                + escapeHtml(emptyText)
                + '</p>';
            lastPostId = 0;
            return;
        }

        var html = '';

        posts.forEach(function (post, index) {
            html += buildPostMarkup(post, index === posts.length - 1);
        });

        preservePageScroll(function () {
            feed.innerHTML = html;
            lastPostId = Number(posts[posts.length - 1].id) || 0;
            scrollFeedToLatest(forceScroll);
        });
    }

    function fetchPosts(forceScroll) {
        if (!feedUrl) {
            return Promise.resolve();
        }

        return fetch(feedUrl, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success || !payload.data) {
                    return;
                }

                var posts = payload.data.posts || [];
                var newestId = posts.length > 0 ? Number(posts[posts.length - 1].id) : 0;
                var hasNewPosts = newestId !== lastPostId;

                if (hasNewPosts || feed.children.length === 0) {
                    renderPosts(posts, forceScroll || hasNewPosts);
                }
            })
            .catch(function () {
                // Ignore transient polling errors.
            });
    }

    function startPolling() {
        if (pollTimer !== null) {
            return;
        }

        pollTimer = window.setInterval(function () {
            fetchPosts(false);
        }, pollIntervalMs);
    }

    function bootstrapLastPostId() {
        var nodes = feed.querySelectorAll('[data-post-id]');

        if (nodes.length === 0) {
            lastPostId = 0;
            return;
        }

        lastPostId = Number(nodes[nodes.length - 1].getAttribute('data-post-id')) || 0;
    }

    function submitPost() {
        if (!form || !postUrl || isSubmitting) {
            return;
        }

        var textarea = document.getElementById('discussion-body');
        var submitButton = form.querySelector('button[type="submit"]');
        var body = textarea ? String(textarea.value || '').trim() : '';

        if (body === '') {
            return;
        }

        isSubmitting = true;

        if (submitButton) {
            submitButton.disabled = true;
        }

        var formData = new FormData(form);

        fetch(postUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: formData,
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.payload || !result.payload.success) {
                    var message = result.payload && result.payload.error
                        ? result.payload.error.message
                        : 'Error';
                    showAlert(message, 'error');
                    return;
                }

                if (textarea) {
                    textarea.value = '';
                }

                showAlert(successText, 'success');
                return fetchPosts(true).then(function () {
                    preservePageScroll(function () {
                        scrollFeedToLatest(true);

                        if (textarea && typeof textarea.focus === 'function') {
                            textarea.focus({ preventScroll: true });
                        }
                    });
                });
            })
            .catch(function () {
                showAlert('Error', 'error');
            })
            .finally(function () {
                isSubmitting = false;

                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
    }

    if (form && postUrl) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitPost();
        });

        var textarea = document.getElementById('discussion-body');

        if (textarea) {
            textarea.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' || event.shiftKey) {
                    return;
                }

                event.preventDefault();
                submitPost();
            });
        }
    }

    bootstrapLastPostId();
    preservePageScroll(function () {
        scrollFeedToLatest(true);
    });

    if (window.location.hash === '#discussion-board') {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }

    startPolling();
})();
