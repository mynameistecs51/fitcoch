<?php
$result = $result ?? null;
$ticket = $ticket ?? null;
$inputClass = 'rounded-lg border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300 align-top';

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-xs text-brand-600 dark:text-brand-500 font-semibold mb-1">
                <?= escape($course->title) ?> · <?= escape($module->title) ?>
            </p>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape($quiz->title) ?></h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?= escape(__('quizzes.passing_score', ['score' => (string) $quiz->passingScorePct])) ?>
            </p>
        </div>
        <a href="<?= escape(url('/courses/' . $course->id)) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('quizzes.back_to_syllabus')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
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
            <div class="p-5 rounded-2xl border <?= $result['passed'] ? 'border-brand-500/30 bg-brand-500/10' : 'border-amber-500/30 bg-amber-500/10' ?>">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                    <?= escape($result['passed'] ? __('quizzes.result_passed') : __('quizzes.result_failed')) ?>
                </h2>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    <?= escape(__('quizzes.result_score', ['score' => (string) $result['score_pct']])) ?>
                </p>
                <?php if ($result['passed'] && $quiz->quizType === 'readiness'): ?>
                    <p class="text-sm text-brand-700 dark:text-brand-accent mt-2">
                        <i class="fa-solid fa-ticket mr-1"></i>
                        <?= escape(__('quizzes.readiness_unlocked')) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($ticket !== null && $ticket->isOpen() && $result === null): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('quizzes.ticket_already_open')) ?>
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="<?= escape(url('/quizzes/' . $quiz->id . '/attempts')) ?>"
            data-progress
            data-progress-label="<?= escape(__('quizzes.submitting')) ?>"
            data-progress-processing="<?= escape(__('progress.processing')) ?>"
        >
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800 mb-4">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?> w-16">#</th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.question')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.answer')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($questions as $index => $question): ?>
                            <tr>
                                <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent"><?= escape((string) ($index + 1)) ?></td>
                                <td class="<?= escape($tdClass) ?> font-medium"><?= escape($question->questionText) ?></td>
                                <td class="<?= escape($tdClass) ?>">
                                    <div class="space-y-2">
                                        <?php foreach ($question->options as $option): ?>
                                            <label class="flex items-start gap-2 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="responses[<?= (int) $question->id ?>]"
                                                    value="<?= (int) $option['option_number'] ?>"
                                                    required
                                                    class="mt-1 <?= escape($inputClass) ?>"
                                                >
                                                <span><?= escape($option['option_text']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                <?= escape(__('quizzes.submit')) ?>
            </button>
        </form>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
