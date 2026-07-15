(function () {
    const form = document.getElementById('quiz-questions-form');
    const blocksContainer = document.getElementById('question-blocks');
    const addButton = document.getElementById('add-question-block-btn');

    if (!form || !blocksContainer || !addButton) {
        return;
    }

    const labels = JSON.parse(form.dataset.labels || '{}');

    function nextIndex() {
        const blocks = blocksContainer.querySelectorAll('.question-block');
        let max = -1;

        blocks.forEach(function (block) {
            const index = parseInt(block.dataset.index || '0', 10);
            if (index > max) {
                max = index;
            }
        });

        return max + 1;
    }

    function updateBlockTitles() {
        const blocks = blocksContainer.querySelectorAll('.question-block');

        blocks.forEach(function (block, position) {
            const title = block.querySelector('.question-block-title');
            if (title) {
                title.textContent = (labels.question_number || 'Question :number').replace(':number', String(position + 1));
            }

            const removeBtn = block.querySelector('.remove-question-block-btn');
            if (removeBtn) {
                removeBtn.hidden = blocks.length <= 1;
            }
        });
    }

    function createBlock(index) {
        const block = document.createElement('div');
        block.className = 'question-block rounded-2xl border border-slate-200 dark:border-slate-800 p-4 md:p-5 space-y-4 bg-slate-50/60 dark:bg-slate-950/40';
        block.dataset.index = String(index);

        let optionsHtml = '';
        for (let i = 1; i <= 4; i++) {
            optionsHtml +=
                '<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">' +
                escapeHtml(labels.option || 'Option') + ' ' + i +
                '</label><input type="text" name="questions[' + index + '][option_' + i + ']" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></div>';
        }

        let correctOptionsHtml = '';
        for (let i = 1; i <= 4; i++) {
            correctOptionsHtml += '<option value="' + i + '">' + escapeHtml(labels.option || 'Option') + ' ' + i + '</option>';
        }

        block.innerHTML =
            '<div class="flex items-center justify-between gap-3">' +
                '<p class="text-sm font-bold text-slate-900 dark:text-white question-block-title"></p>' +
                '<button type="button" class="remove-question-block-btn text-xs text-red-600 dark:text-red-400 hover:underline font-semibold">' +
                    '<i class="fa-solid fa-xmark mr-1"></i>' + escapeHtml(labels.remove_question || 'Remove') +
                '</button>' +
            '</div>' +
            '<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">' +
                escapeHtml(labels.question_text || 'Question text') +
            '</label><textarea name="questions[' + index + '][question_text]" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea></div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' + optionsHtml + '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                '<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">' +
                    escapeHtml(labels.correct_option || 'Correct answer') +
                '</label><select name="questions[' + index + '][correct_option]" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">' +
                    correctOptionsHtml +
                '</select></div>' +
                '<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">' +
                    escapeHtml(labels.points || 'Points') +
                '</label><input type="number" name="questions[' + index + '][points]" min="1" max="100" value="10" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></div>' +
            '</div>';

        return block;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    addButton.addEventListener('click', function () {
        const block = createBlock(nextIndex());
        blocksContainer.appendChild(block);
        updateBlockTitles();
        block.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    blocksContainer.addEventListener('click', function (event) {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const removeBtn = target.closest('.remove-question-block-btn');

        if (!removeBtn) {
            return;
        }

        const blocks = blocksContainer.querySelectorAll('.question-block');

        if (blocks.length <= 1) {
            return;
        }

        const block = removeBtn.closest('.question-block');

        if (block) {
            block.remove();
            updateBlockTitles();
        }
    });

    updateBlockTitles();
})();
