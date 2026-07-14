<?php
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape(__('quizzes.instructor.readiness_title')) ?></h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?= escape($course->title) ?> · <?= escape($module->title) ?> · <?= escape($cohort->name) ?>
            </p>
        </div>
        <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('courses.instructor.back')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
        <?php if ($success === 'overridden'): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('quizzes.instructor.override_success')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(is_string($error) ? $error : __('errors.validation_failed')) ?>
            </div>
        <?php endif; ?>

        <?php if ($quiz === null): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('quizzes.instructor.no_quiz')) ?></p>
        <?php else: ?>
            <p class="text-sm text-slate-600 dark:text-slate-300">
                <?= escape(__('quizzes.instructor.readiness_hint', ['score' => (string) $quiz->passingScorePct])) ?>
            </p>

            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.learner')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.ticket_status')) ?></th>
                            <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($tickets as $row): ?>
                            <?php $status = (string) ($row['status'] ?? 'locked'); ?>
                            <tr>
                                <td class="<?= escape($tdClass) ?>">
                                    <div class="font-medium text-slate-900 dark:text-slate-200">
                                        <?= escape(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= escape((string) ($row['email'] ?? '')) ?></div>
                                </td>
                                <td class="<?= escape($tdClass) ?>">
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold <?= $status === 'locked' ? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' : 'bg-brand-500/15 text-brand-700 dark:text-brand-accent' ?>">
                                        <?= escape(__('quizzes.ticket_status.' . $status)) ?>
                                    </span>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-right">
                                    <?php if ($status === 'locked'): ?>
                                        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/readiness/' . $row['user_id'] . '/override')) ?>" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                            <button type="submit" class="text-xs text-brand-600 dark:text-brand-500 hover:underline font-semibold">
                                                <?= escape(__('quizzes.instructor.override')) ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
