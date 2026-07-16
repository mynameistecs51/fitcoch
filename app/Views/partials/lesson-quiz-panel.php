<?php
/** @var \App\Models\Quiz $quiz */
/** @var array<int, \App\Models\Question> $questions */
/** @var array<string, mixed>|null $result */
/** @var \App\Models\ReadinessTicket|null $ticket */
/** @var string|null $error */
$result = $result ?? null;
$ticket = $ticket ?? null;
$error = $error ?? null;
$latestAttempt = $latestAttempt ?? null;
$retake = $retake ?? false;
$retakeQuizUrl = $retakeQuizUrl ?? url('/quizzes/' . $quiz->id . '?retake=1');
$showPreviousAttempt = $result === null && $latestAttempt !== null && !$retake;
$compact = $compact ?? false;
$inputClass = 'rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-200';
?>
<section class="lesson-quiz-panel rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/70">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clipboard-question text-brand-500"></i>
            <?= escape(__('lesson.pretest_title')) ?>
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            <?= escape($quiz->title) ?> · <?= escape(__('quizzes.passing_score', ['score' => (string) $quiz->passingScorePct])) ?>
        </p>
    </div>

    <div class="p-5 md:p-6 space-y-5">
        <?php if ($error === 'csrf' || $error === __('errors.invalid_csrf')): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('errors.invalid_csrf')) ?>
            </div>
        <?php elseif ($error === 'validation'): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('quizzes.validation.answer_all')) ?>
            </div>
        <?php elseif (is_string($error) && $error !== ''): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <div
                data-quiz-passed-result
                class="p-5 rounded-2xl border ux-alert-enter <?= $result['passed'] ? 'border-brand-500/30 bg-brand-500/10 quiz-passed-card' : 'border-amber-500/30 bg-amber-500/10' ?>"
            >
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                    <?= escape($result['passed'] ? __('quizzes.result_passed') : __('quizzes.result_failed')) ?>
                </h3>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    <?= escape(__('quizzes.result_score', ['score' => (string) $result['score_pct']])) ?>
                </p>
                <?php if ($result['passed'] && $quiz->quizType === 'readiness'): ?>
                    <p class="text-sm text-brand-700 dark:text-brand-accent mt-2">
                        <i class="fa-solid fa-ticket mr-1"></i>
                        <?= escape(__('quizzes.readiness_unlocked')) ?>
                    </p>
                <?php elseif (!$result['passed']): ?>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <?php if (!empty($retakeLessonUrl)): ?>
                            <a href="<?= escape((string) $retakeLessonUrl) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold hover:border-brand-500/40 transition">
                                <i class="fa-solid fa-circle-play text-brand-500"></i>
                                <?= escape(__('dashboard.retake_lesson')) ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= escape(url('/quizzes/' . $quiz->id)) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-500 text-slate-950 text-sm font-bold hover:bg-brand-accent transition">
                            <i class="fa-solid fa-rotate-right"></i>
                            <?= escape(__('dashboard.retake_quiz')) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($result['passed'])): ?>
                <script>
                window.FitCochQuizCelebration = { passed: true, score: <?= (int) $result['score_pct'] ?> };
                </script>
                <script src="<?= escape(url('/assets/quiz-celebration.js')) ?>"></script>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($ticket !== null && $ticket->isOpen() && $result === null && $latestAttempt === null && !$retake): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <i class="fa-solid fa-ticket mr-1"></i>
                <?= escape(__('quizzes.ticket_pre_unlocked_hint')) ?>
            </div>
        <?php endif; ?>

        <?php if ($showPreviousAttempt): ?>
            <div class="p-5 rounded-2xl border border-brand-500/30 bg-brand-500/10 space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        <?= escape(__('quizzes.completed_title')) ?>
                    </h3>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">
                        <?= escape(__('quizzes.completed_score', ['score' => (string) $latestAttempt['score_pct']])) ?>
                    </p>
                    <?php if ($latestAttempt['passed']): ?>
                        <p class="text-sm text-brand-700 dark:text-brand-accent mt-2">
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            <?= escape(__('quizzes.result_passed')) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-2">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            <?= escape(__('quizzes.result_failed')) ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($ticket !== null && $ticket->isOpen() && $quiz->quizType === 'readiness'): ?>
                        <p class="text-sm text-brand-700 dark:text-brand-accent mt-2">
                            <i class="fa-solid fa-ticket mr-1"></i>
                            <?= escape(__('quizzes.readiness_unlocked')) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="space-y-4">
                    <?php foreach ($questions as $index => $question): ?>
                        <?php $selectedOption = $latestAttempt['responses'][$question->id] ?? null; ?>
                        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4 md:p-5 <?= $index % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50 dark:bg-slate-950/50' ?>">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <h3 class="text-sm md:text-base font-semibold text-slate-900 dark:text-white leading-relaxed">
                                    <?= escape($question->questionText) ?>
                                </h3>
                                <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    <?= escape((string) ($index + 1)) ?>/<?= escape((string) count($questions)) ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php foreach ($question->options as $option): ?>
                                    <?php
                                        $isSelected = $selectedOption !== null && (int) $option['option_number'] === (int) $selectedOption;
                                        $optionClass = $isSelected
                                            ? 'border-brand-500/50 bg-brand-500/10 ring-2 ring-brand-500/20'
                                            : 'border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 opacity-70';
                                    ?>
                                    <div class="flex items-start gap-3 p-3 rounded-xl border <?= escape($optionClass) ?>">
                                        <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center <?= $isSelected ? 'border-brand-500 bg-brand-500 text-slate-950' : 'border-slate-300 dark:border-slate-600' ?>">
                                            <?php if ($isSelected): ?>
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                                            <?= escape($option['option_text']) ?>
                                            <?php if ($isSelected): ?>
                                                <span class="block text-[11px] font-semibold text-brand-700 dark:text-brand-accent mt-1">
                                                    <?= escape(__('quizzes.your_answer')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <?php if (!empty($retakeLessonUrl)): ?>
                        <a href="<?= escape((string) $retakeLessonUrl) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold hover:border-brand-500/40 transition">
                            <i class="fa-solid fa-circle-play text-brand-500"></i>
                            <?= escape(__('dashboard.retake_lesson')) ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= escape($retakeQuizUrl) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-500 text-slate-950 text-sm font-bold hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                        <i class="fa-solid fa-rotate-right"></i>
                        <?= escape(__('quizzes.retake_button')) ?>
                    </a>
                </div>
            </div>
        <?php elseif ($result === null && ($retake || $latestAttempt === null)): ?>
            <?php if ($questions === []): ?>
                <div class="p-5 rounded-2xl border border-dashed border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200 text-sm">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                    <?= escape(__('quizzes.no_questions_learner')) ?>
                </div>
            <?php else: ?>
            <form
                method="POST"
                action="<?= escape(url('/quizzes/' . $quiz->id . '/attempts')) ?>"
                data-quiz-form
                data-quiz-validation-message="<?= escape(__('quizzes.validation.answer_all')) ?>"
                data-progress
                data-progress-label="<?= escape(__('quizzes.submitting')) ?>"
                data-progress-processing="<?= escape(__('progress.processing')) ?>"
                class="space-y-5"
                novalidate
            >
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <?php foreach ($questions as $index => $question): ?>
                    <article
                        data-quiz-question="<?= (int) $question->id ?>"
                        class="lesson-quiz-question rounded-2xl border border-slate-200 dark:border-slate-800 p-4 md:p-5 <?= $index % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50 dark:bg-slate-950/50' ?>"
                    >
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <h3 class="text-sm md:text-base font-semibold text-slate-900 dark:text-white leading-relaxed">
                                <?= escape($question->questionText) ?>
                            </h3>
                            <span class="shrink-0 text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <?= escape((string) ($index + 1)) ?>/<?= escape((string) count($questions)) ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php foreach ($question->options as $option): ?>
                                <label class="lesson-quiz-option flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-brand-500/40 transition">
                                    <input
                                        type="radio"
                                        name="responses[<?= (int) $question->id ?>]"
                                        value="<?= (int) $option['option_number'] ?>"
                                        class="mt-1 shrink-0 <?= escape($inputClass) ?>"
                                    >
                                    <span class="text-sm leading-relaxed text-slate-700 dark:text-slate-200"><?= escape($option['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if (!$compact): ?>
                    <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                        <?= escape(__('quizzes.submit')) ?>
                    </button>
                <?php endif; ?>
            </form>
            <script src="<?= escape(url('/assets/quiz-submit.js')) ?>"></script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
