<?php

$report = $report ?? null;
$course = $report['course'] ?? null;
$cohort = $report['cohort'] ?? null;
$summary = $report['summary'] ?? [];
$modules = $report['modules'] ?? [];
$learners = $report['learners'] ?? [];

$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-brand-500"></i>
                <?= escape(__('courses.instructor.progress_title')) ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?= escape($course?->title ?? '') ?>
                <?php if ($cohort !== null): ?>
                    · <?= escape($cohort->name) ?>
                <?php endif; ?>
            </p>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('courses.instructor.progress_stats.enrolled')) ?></p>
            <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-accent mt-2"><?= escape((string) ($summary['total_enrolled'] ?? 0)) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('courses.instructor.progress_stats.avg_progress')) ?></p>
            <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-accent mt-2"><?= escape((string) ($summary['avg_progress_pct'] ?? 0)) ?>%</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('courses.instructor.progress_stats.lessons')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2">
                <?= escape(number_format((float) ($summary['lessons_completed_avg'] ?? 0), 1)) ?>/<?= escape((string) ($summary['lessons_total'] ?? 0)) ?>
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2"><?= escape(__('courses.instructor.progress_stats.lessons_hint')) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('courses.instructor.progress_stats.quizzes')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2">
                <?= escape(number_format((float) ($summary['quizzes_passed_avg'] ?? 0), 1)) ?>/<?= escape((string) ($summary['quizzes_total'] ?? 0)) ?>
            </p>
            <?php if (($summary['average_quiz_score'] ?? null) !== null): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    <?= escape(__('dashboard.stats.average_score', ['score' => (string) $summary['average_quiz_score']])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape(__('courses.instructor.module_overview_title')) ?></h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.instructor.module_overview_subtitle')) ?></p>
        </div>

        <?php if ($modules === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
        <?php else: ?>
            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.module')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.passed')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.in_progress')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.failed')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.not_started')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.video_avg')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_table.quiz_pass_rate')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($modules as $moduleRow): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                    <?= escape($moduleRow['module']->title) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-brand-600 dark:text-brand-accent font-bold"><?= escape((string) $moduleRow['learners_passed']) ?></td>
                                <td class="<?= escape($tdClass) ?>"><?= escape((string) $moduleRow['learners_in_progress']) ?></td>
                                <td class="<?= escape($tdClass) ?> text-amber-600 dark:text-amber-400"><?= escape((string) $moduleRow['learners_failed']) ?></td>
                                <td class="<?= escape($tdClass) ?> text-slate-400"><?= escape((string) $moduleRow['learners_not_started']) ?></td>
                                <td class="<?= escape($tdClass) ?>">
                                    <?= $moduleRow['video_nugget'] !== null
                                        ? escape((string) $moduleRow['avg_video_progress']) . '%'
                                        : '—' ?>
                                </td>
                                <td class="<?= escape($tdClass) ?>">
                                    <?= $moduleRow['quiz_pass_rate'] !== null
                                        ? escape((string) $moduleRow['quiz_pass_rate']) . '%'
                                        : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape(__('courses.instructor.learners_title')) ?></h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.instructor.learners_subtitle')) ?></p>
        </div>

        <?php if ($learners === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.instructor.no_learners')) ?></p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($learners as $learner): ?>
                    <details class="group rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <summary class="cursor-pointer list-none p-4 md:p-5 bg-slate-50/70 dark:bg-slate-950/50 hover:bg-slate-100/80 dark:hover:bg-slate-900 transition">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        <?= escape(trim($learner['first_name'] . ' ' . $learner['last_name'])) ?>
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape($learner['email']) ?></p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <?= escape(__('courses.instructor.enrolled_at', ['date' => $learner['enrolled_at']])) ?>
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 shrink-0">
                                    <div class="text-right">
                                        <p class="text-[11px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.progress')) ?></p>
                                        <p class="text-xl font-extrabold text-brand-600 dark:text-brand-accent"><?= escape((string) $learner['progress_pct']) ?>%</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[11px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.lessons')) ?></p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">
                                            <?= escape((string) $learner['lessons_completed']) ?>/<?= escape((string) $learner['lessons_total']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[11px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.quizzes')) ?></p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">
                                            <?= escape((string) $learner['quizzes_passed']) ?>/<?= escape((string) $learner['quizzes_total']) ?>
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-brand-600 dark:text-brand-500">
                                        <?= escape(__('courses.instructor.view_modules')) ?>
                                        <i class="fa-solid fa-chevron-down transition group-open:rotate-180"></i>
                                    </span>
                                </div>
                            </div>
                        </summary>
                        <div class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php foreach ($learner['modules'] as $moduleRow): ?>
                                <?php
                                    $status = (string) ($moduleRow['status'] ?? 'not_started');
                                    $statusClass = match ($status) {
                                        'passed' => 'bg-brand-500/10 text-brand-700 dark:text-brand-accent',
                                        'failed' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                        'in_progress' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
                                        default => 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
                                    };
                                ?>
                                <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape($moduleRow['module']->title) ?></p>
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold <?= escape($statusClass) ?>">
                                                <?= escape(__('dashboard.module_status.' . $status)) ?>
                                            </span>
                                            <?php if ($moduleRow['video_progress'] !== null): ?>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                                    <?= escape(__('courses.instructor.video_progress', ['pct' => (string) $moduleRow['video_progress']])) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($moduleRow['latest_score'] !== null): ?>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                                    <?= escape(__('dashboard.module_score', ['score' => (string) $moduleRow['latest_score']])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<style>
    details > summary::-webkit-details-marker {
        display: none;
    }
</style>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
