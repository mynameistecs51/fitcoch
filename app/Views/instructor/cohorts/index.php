<?php

$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-users-between-lines text-brand-500"></i>
                <?= escape(__('cohorts.instructor.title')) ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape($course->title) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                <?= escape(__('courses.instructor.edit')) ?>
            </a>
            <a href="<?= escape(url('/instructor/courses')) ?>" class="text-sm text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-500">
                <?= escape(__('courses.instructor.back')) ?>
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
            <?= escape(__('cohorts.instructor.success.' . $success)) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape($error === 'validation' ? __('errors.validation_failed') : (is_string($error) ? $error : __('errors.validation_failed'))) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape(__('cohorts.instructor.create_title')) ?></h2>
        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts')) ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.name')) ?></label>
                <input type="text" name="name" required class="<?= escape($inputClass) ?>" placeholder="<?= escape(__('cohorts.form.name_placeholder')) ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.start_date')) ?></label>
                <input type="date" name="start_date" required class="<?= escape($inputClass) ?>" value="<?= escape(date('Y-m-d')) ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.end_date')) ?></label>
                <input type="date" name="end_date" required class="<?= escape($inputClass) ?>" value="<?= escape(date('Y-m-d', strtotime('+1 year'))) ?>">
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                    <?= escape(__('cohorts.instructor.create')) ?>
                </button>
            </div>
        </form>
    </div>

    <?php if ($cohorts === []): ?>
        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 text-center text-sm text-slate-500 dark:text-slate-400">
            <?= escape(__('cohorts.instructor.empty')) ?>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($cohorts as $entry): ?>
                <?php $cohort = $entry['cohort']; ?>
                <article class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape($cohort->name) ?></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <?= escape(__('cohorts.instructor.enrollment_count', ['count' => (string) $entry['enrollment_count']])) ?>
                            </p>
                        </div>
                        <a href="<?= escape(url('/instructor/analytics/cohort/' . $cohort->id)) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-500/10 text-brand-700 dark:text-brand-accent hover:bg-brand-500/20 transition shrink-0">
                            <i class="fa-solid fa-chart-pie"></i><?= escape(__('analytics.instructor.view')) ?>
                        </a>
                    </div>

                    <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts/' . $cohort->id)) ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.name')) ?></label>
                            <input type="text" name="name" required value="<?= escape($cohort->name) ?>" class="<?= escape($inputClass) ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.start_date')) ?></label>
                            <input type="date" name="start_date" required value="<?= escape($cohort->startDate) ?>" class="<?= escape($inputClass) ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.form.end_date')) ?></label>
                            <input type="date" name="end_date" required value="<?= escape($cohort->endDate) ?>" class="<?= escape($inputClass) ?>">
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                                <?= escape(__('cohorts.instructor.save')) ?>
                            </button>
                        </div>
                    </form>

                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('cohorts.instructor.enrollments_title')) ?></h3>

                        <?php if ($availableLearners !== []): ?>
                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts/' . $cohort->id . '/enroll')) ?>" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= escape(__('cohorts.instructor.add_learner')) ?></label>
                                    <select name="user_id" required class="<?= escape($inputClass) ?>">
                                        <option value=""><?= escape(__('cohorts.instructor.select_learner')) ?></option>
                                        <?php foreach ($availableLearners as $learner): ?>
                                            <option value="<?= escape((string) $learner['user_id']) ?>">
                                                <?= escape(trim($learner['first_name'] . ' ' . $learner['last_name']) . ' (' . $learner['email'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="px-5 py-3 bg-slate-800 dark:bg-slate-700 text-white font-bold rounded-xl hover:bg-slate-700 dark:hover:bg-slate-600 text-sm whitespace-nowrap">
                                    <?= escape(__('cohorts.instructor.enroll')) ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-950">
                                    <tr>
                                        <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.learner')) ?></th>
                                        <th class="<?= escape($thClass) ?>"><?= escape(__('cohorts.table.enrolled_at')) ?></th>
                                        <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    <?php if ($entry['enrollments'] === []): ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                                <?= escape(__('cohorts.instructor.no_enrollments')) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($entry['enrollments'] as $enrollment): ?>
                                            <tr>
                                                <td class="<?= escape($tdClass) ?>">
                                                    <div class="font-medium text-slate-900 dark:text-slate-200">
                                                        <?= escape(trim(($enrollment['first_name'] ?? '') . ' ' . ($enrollment['last_name'] ?? ''))) ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= escape((string) ($enrollment['email'] ?? '')) ?></div>
                                                </td>
                                                <td class="<?= escape($tdClass) ?>"><?= escape((string) ($enrollment['enrolled_at'] ?? '')) ?></td>
                                                <td class="<?= escape($tdClass) ?> text-right">
                                                    <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts/' . $cohort->id . '/enrollments/' . $enrollment['user_id'] . '/drop')) ?>" class="inline" onsubmit="return confirm('<?= escape(__('cohorts.instructor.confirm_drop')) ?>');">
                                                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                        <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold">
                                                            <?= escape(__('cohorts.instructor.drop')) ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
