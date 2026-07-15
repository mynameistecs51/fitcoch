<?php

ob_start();
$overview = $overview ?? ['summary' => [], 'courses' => [], 'retake_items' => []];
$summary = $overview['summary'] ?? [];
$courses = $overview['courses'] ?? [];
$retakeItems = $overview['retake_items'] ?? [];
?>
<section class="space-y-8">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-white via-slate-50 to-brand-50 dark:from-slate-900 dark:via-slate-900 dark:to-brand-dark/20 p-6 md:p-10 border border-slate-200 dark:border-slate-800">
        <div class="max-w-3xl space-y-3 relative">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-accent border border-brand-500/20">
                <?= escape(__('dashboard.badge')) ?>
            </span>
            <h1 class="text-2xl md:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight">
                <?= escape(__('dashboard.welcome', ['name' => $user->firstName])) ?>
            </h1>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                <?= escape(__('dashboard.overview_subtitle')) ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.enrolled')) ?></p>
            <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-accent mt-2"><?= escape((string) ($summary['enrolled_courses'] ?? 0)) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.progress')) ?></p>
            <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-accent mt-2"><?= escape((string) ($summary['overall_progress'] ?? 0)) ?>%</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.lessons')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2">
                <?= escape((string) ($summary['lessons_completed'] ?? 0)) ?>/<?= escape((string) ($summary['lessons_total'] ?? 0)) ?>
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.quizzes')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2">
                <?= escape((string) ($summary['quizzes_passed'] ?? 0)) ?>/<?= escape((string) ($summary['quizzes_total'] ?? 0)) ?>
            </p>
            <?php if (($summary['average_quiz_score'] ?? null) !== null): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    <?= escape(__('dashboard.stats.average_score', ['score' => (string) $summary['average_quiz_score']])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($summary['reviews_due'] ?? 0) > 0): ?>
        <div class="rounded-3xl border border-brand-500/30 bg-gradient-to-r from-brand-500/10 to-brand-500/5 p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-brain text-brand-500"></i>
                    <?= escape(__('dashboard.reviews_title')) ?>
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                    <?= escape(__('dashboard.reviews_hint', ['count' => (string) ($summary['reviews_due'] ?? 0)])) ?>
                </p>
            </div>
            <a href="<?= escape(url('/review/daily')) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20 shrink-0">
                <?= escape(__('dashboard.reviews_cta')) ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($retakeItems !== []): ?>
        <div class="rounded-3xl border border-amber-500/30 bg-amber-500/10 p-5 md:p-6 space-y-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-rotate-right text-amber-600 dark:text-amber-400"></i>
                    <?= escape(__('dashboard.retake_section_title')) ?>
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1"><?= escape(__('dashboard.retake_section_hint')) ?></p>
            </div>
            <div class="space-y-3">
                <?php foreach ($retakeItems as $item): ?>
                    <div class="rounded-2xl border border-amber-500/20 bg-white/80 dark:bg-slate-900/80 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white"><?= escape($item['quiz_title']) ?></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <?= escape($item['course_title']) ?> · <?= escape($item['module_title']) ?>
                            </p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-2">
                                <?= escape(__('dashboard.retake_score', [
                                    'score' => (string) $item['score_pct'],
                                    'passing' => (string) $item['passing_score'],
                                ])) ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <?php if (!empty($item['lesson_url'])): ?>
                                <a href="<?= escape((string) $item['lesson_url']) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold hover:border-brand-500/40 transition">
                                    <i class="fa-solid fa-circle-play text-brand-500"></i>
                                    <?= escape(__('dashboard.retake_lesson')) ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= escape((string) $item['quiz_url']) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-500 text-slate-950 text-sm font-bold hover:bg-brand-accent transition">
                                <i class="fa-solid fa-rotate-right"></i>
                                <?= escape(__('dashboard.retake_quiz')) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-book-open text-brand-500"></i>
                    <?= escape(__('dashboard.my_courses_title')) ?>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('dashboard.my_courses_subtitle')) ?></p>
            </div>
            <a href="<?= escape(url('/courses')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-semibold">
                <?= escape(__('dashboard.browse_courses')) ?>
            </a>
        </div>

        <?php if ($courses === []): ?>
            <div class="text-center py-10">
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.empty')) ?></p>
                <a href="<?= escape(url('/courses')) ?>" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl bg-brand-500 text-slate-950 font-bold text-sm hover:bg-brand-accent transition">
                    <?= escape(__('dashboard.browse_courses')) ?>
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($courses as $entry): ?>
                    <?php $course = $entry['course']; ?>
                    <article class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape($course->title) ?></h3>
                                <?php if ($course->description): ?>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2"><?= escape($course->description) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-4 shrink-0">
                                <div class="text-right">
                                    <p class="text-[11px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.stats.progress')) ?></p>
                                    <p class="text-xl font-extrabold text-brand-600 dark:text-brand-accent"><?= escape((string) $entry['progress_pct']) ?>%</p>
                                </div>
                                <?php if (!empty($entry['resume_url'])): ?>
                                    <a href="<?= escape((string) $entry['resume_url']) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-slate-950 font-bold text-sm hover:bg-brand-accent transition">
                                        <i class="fa-solid fa-play"></i>
                                        <?= escape(__('dashboard.continue_learning')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($entry['modules'] !== []): ?>
                            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($entry['modules'] as $moduleRow): ?>
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
                                                <?php if ($moduleRow['latest_score'] !== null): ?>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                                        <?= escape(__('dashboard.module_score', ['score' => (string) $moduleRow['latest_score']])) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <?php if (!empty($moduleRow['lesson_url'])): ?>
                                                <a href="<?= escape((string) $moduleRow['lesson_url']) ?>" class="text-xs font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                                                    <?= escape(__('dashboard.open_lesson')) ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($moduleRow['quiz_url'])): ?>
                                                <a href="<?= escape((string) $moduleRow['quiz_url']) ?>" class="text-xs font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                                                    <?= escape(__('dashboard.open_quiz')) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'dashboard';
require base_path('app/Views/layouts/app.php');
