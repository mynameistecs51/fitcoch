<?php
/** @var \App\Models\Quiz $quiz */
/** @var array<int, \App\Models\Question> $questions */
/** @var array<string, mixed>|null $result */
/** @var \App\Models\ReadinessTicket|null $ticket */
/** @var string|null $error */
$result = $result ?? null;
$ticket = $ticket ?? null;
$error = $error ?? null;
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
                class="p-5 rounded-2xl border <?= $result['passed'] ? 'border-brand-500/30 bg-brand-500/10 quiz-passed-card' : 'border-amber-500/30 bg-amber-500/10' ?>"
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

        <?php if ($ticket !== null && $ticket->isOpen() && $result === null): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('quizzes.ticket_already_open')) ?>
            </div>
        <?php elseif ($result === null): ?>
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
    </div>
</section>
