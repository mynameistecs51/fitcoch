<?php

$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$instructorQuickLinkClass = 'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-500/30 hover:text-brand-600 dark:hover:text-brand-accent transition';
$metrics = $metrics ?? [];
$readinessPct = (int) ($metrics['readiness_pct'] ?? 0);
$alertTriggered = (bool) ($metrics['alert_triggered'] ?? false);

$heroTitle = __('analytics.instructor.title');
$heroSubtitle = $course->title . ' · ' . $cohort->name . ' · ' . $selectedModule->title;
$heroBadgeIcon = 'fa-chart-pie';
ob_start();
?>
<a href="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts')) ?>" class="<?= escape($instructorQuickLinkClass) ?>">
    <i class="fa-solid fa-user-group text-sky-500"></i>
    <?= escape(__('cohorts.instructor.manage')) ?>
</a>
<a href="<?= escape(url('/instructor/courses/' . $course->id . '/progress')) ?>" class="<?= escape($instructorQuickLinkClass) ?>">
    <i class="fa-solid fa-chart-line text-brand-500"></i>
    <?= escape(__('courses.instructor.progress_title')) ?>
</a>
<a href="<?= escape(url('/instructor/courses')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
    <i class="fa-solid fa-arrow-left text-xs"></i>
    <?= escape(__('courses.instructor.back')) ?>
</a>
<?php
$heroActions = ob_get_clean();

ob_start();
?>
<section class="space-y-8">
    <?php require base_path('app/Views/partials/instructor-page-hero.php'); ?>

    <form method="GET" action="<?= escape(url('/instructor/analytics/cohort/' . $cohort->id)) ?>" class="ux-card p-5 md:p-6 flex flex-col sm:flex-row gap-3 sm:items-end">
        <div class="flex-1">
            <label for="module" class="ux-label"><?= escape(__('analytics.instructor.select_module')) ?></label>
            <select id="module" name="module" class="ux-input" onchange="this.form.submit()">
                <?php foreach ($modules as $module): ?>
                    <option value="<?= escape((string) $module->id) ?>" <?= $module->id === $selectedModule->id ? 'selected' : '' ?>>
                        <?= escape($module->title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="px-5 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm">
            <?= escape(__('analytics.instructor.refresh')) ?>
        </button>
    </form>

    <?php if ($alertTriggered): ?>
        <div class="p-6 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-2xl flex items-start gap-4">
            <div class="p-3 bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-400 rounded-xl font-bold shrink-0">!</div>
            <div>
                <h4 class="font-bold text-red-800 dark:text-red-400"><?= escape(__('analytics.instructor.alert_title')) ?></h4>
                <p class="text-sm text-red-700 dark:text-red-500 mt-1">
                    <?= escape(__('analytics.instructor.alert_message', [
                        'pct' => (string) $readinessPct,
                        'threshold' => '60',
                    ])) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6">
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-users"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('analytics.instructor.total_enrolled')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) ($metrics['total_enrolled'] ?? 0)) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-sky-500/10 text-sky-600 dark:text-sky-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('analytics.instructor.completed_prep')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) ($metrics['completed_prep'] ?? 0)) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6 <?= $alertTriggered ? 'border-red-500/25 bg-gradient-to-br from-red-500/8 to-transparent' : '' ?>">
            <div class="ux-stat-icon bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('analytics.instructor.readiness_index')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold <?= $alertTriggered ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-accent' ?> mt-1">
                <?= escape((string) $readinessPct) ?>%
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
        <div class="ux-card p-6 md:p-8 space-y-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <?= escape(__('analytics.instructor.misconceptions_title')) ?>
            </h2>
            <?php if ($quiz === null): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('analytics.instructor.no_quiz')) ?></p>
            <?php elseif ($topMisconceptions === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('analytics.instructor.no_misconceptions')) ?></p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topMisconceptions as $item): ?>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape($item['question_text']) ?></p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-2">
                                <?= escape(__('analytics.instructor.incorrect_ratio', [
                                    'ratio' => (string) round(((float) $item['incorrect_ratio']) * 100),
                                ])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="ux-card p-6 md:p-8 space-y-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-user-clock text-red-500"></i>
                <?= escape(__('analytics.instructor.at_risk_title')) ?>
            </h2>
            <?php if ($atRiskLearners === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('analytics.instructor.no_at_risk')) ?></p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($atRiskLearners as $row): ?>
                        <div class="rounded-2xl border border-red-200/60 dark:border-red-900/40 bg-red-50/40 dark:bg-red-950/10 p-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    <?= escape(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= escape((string) ($row['email'] ?? '')) ?></p>
                            </div>
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">
                                <?= escape(__('quizzes.ticket_status.' . ($row['status'] ?? 'locked'))) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ux-card p-6 md:p-8 space-y-4">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-users text-brand-500"></i>
            <?= escape(__('analytics.instructor.learners_title')) ?>
        </h2>

        <?php if ($quiz === null): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('analytics.instructor.no_quiz')) ?></p>
        <?php else: ?>
            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.learner')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.latest_score')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.ticket_status')) ?></th>
                            <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($learners as $row): ?>
                            <?php
                            $status = (string) ($row['status'] ?? 'locked');
                            $latestScore = $row['latest_score'] ?? null;
                            $quizPassed = (bool) ($row['quiz_passed'] ?? false);
                            ?>
                            <tr>
                                <td class="<?= escape($tdClass) ?>">
                                    <div class="font-medium text-slate-900 dark:text-slate-200">
                                        <?= escape(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= escape((string) ($row['email'] ?? '')) ?></div>
                                </td>
                                <td class="<?= escape($tdClass) ?>">
                                    <?php if ($latestScore !== null): ?>
                                        <span class="font-semibold <?= $quizPassed ? 'text-brand-600 dark:text-brand-accent' : 'text-amber-600 dark:text-amber-400' ?>">
                                            <?= escape((string) $latestScore) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= escape($tdClass) ?>">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold <?= in_array($status, ['unlocked', 'overridden'], true) ? 'bg-brand-500/15 text-brand-700 dark:text-brand-accent' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
                                        <?= escape(__('quizzes.ticket_status.' . $status)) ?>
                                    </span>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                        <?php if ($status !== 'overridden' && $status !== 'unlocked'): ?>
                                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $selectedModule->id . '/readiness/' . $row['user_id'] . '/override')) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                <button type="submit" class="text-xs font-semibold text-brand-600 dark:text-brand-500 hover:underline">
                                                    <?= escape(__('quizzes.instructor.override')) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($status !== 'locked'): ?>
                                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $selectedModule->id . '/readiness/' . $row['user_id'] . '/lock')) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                <button type="submit" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline">
                                                    <?= escape(__('quizzes.instructor.lock')) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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
