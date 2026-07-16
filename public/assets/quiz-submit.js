(function () {
    function getErrorBanner(form) {
        let banner = form.querySelector('[data-quiz-error]');

        if (!banner) {
            banner = document.createElement('div');
            banner.dataset.quizError = '1';
            banner.className = 'hidden p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm';
            banner.setAttribute('role', 'alert');
            form.prepend(banner);
        }

        return banner;
    }

    function getQuestions(form) {
        return Array.from(form.querySelectorAll('[data-quiz-question]'));
    }

    function isQuestionAnswered(form, questionId) {
        return form.querySelector('input[name="responses[' + questionId + ']"]:checked') !== null;
    }

    function setQuestionError(article, hasError) {
        article.classList.toggle('lesson-quiz-question--error', hasError);
    }

    function hideError(form) {
        const banner = form.querySelector('[data-quiz-error]');
        if (banner) {
            banner.classList.add('hidden');
            banner.textContent = '';
        }
    }

    function showValidationError(form, unansweredArticles) {
        const message = form.dataset.quizValidationMessage || 'Please answer every question before submitting.';
        const banner = getErrorBanner(form);

        banner.textContent = message;
        banner.classList.remove('hidden');

        if (unansweredArticles.length > 0) {
            unansweredArticles[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        banner.classList.add('ux-alert-enter');
    }

    function validateQuizForm(form) {
        const unanswered = [];

        getQuestions(form).forEach(function (article) {
            const questionId = article.dataset.quizQuestion;

            if (!questionId || isQuestionAnswered(form, questionId)) {
                setQuestionError(article, false);
                return;
            }

            setQuestionError(article, true);
            unanswered.push(article);
        });

        if (unanswered.length === 0) {
            hideError(form);
            return true;
        }

        showValidationError(form, unanswered);
        return false;
    }

    function handleSubmit(event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-quiz-form')) {
            return;
        }

        if (!validateQuizForm(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            form.dataset.progressSubmitting = '0';

            if (window.FitCochFormProgress && typeof window.FitCochFormProgress.hide === 'function') {
                window.FitCochFormProgress.hide();
            }
        }
    }

    function handleChange(event) {
        const input = event.target;

        if (!(input instanceof HTMLInputElement) || input.type !== 'radio') {
            return;
        }

        const form = input.closest('[data-quiz-form]');

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const article = input.closest('[data-quiz-question]');

        if (article) {
            setQuestionError(article, false);
        }

        const stillUnanswered = getQuestions(form).some(function (item) {
            const questionId = item.dataset.quizQuestion;
            return questionId && !isQuestionAnswered(form, questionId);
        });

        if (!stillUnanswered) {
            hideError(form);
        }
    }

    document.addEventListener('submit', handleSubmit, true);
    document.addEventListener('change', handleChange);
})();
