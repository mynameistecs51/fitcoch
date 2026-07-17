<?php

ob_start();
$enrollmentCounts = $enrollmentCounts ?? [];
$unreadCounts = $unreadCounts ?? [];
$totalCourses = count($courses);
$publishedCount = count(array_filter(
    $courses,
    static fn ($course): bool => $course->status === 'published'
));
$totalEnrolled = (int) array_sum($enrollmentCounts);
$totalUnread = (int) array_sum($unreadCounts);

$statusClass = static function (string $status): string {
    return match ($status) {
        'published' => 'bg-brand-500/10 text-brand-700 dark:text-brand-accent border-brand-500/20',
        'draft' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20',
        'archived' => 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700',
        default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    };
};

$actionBtnClass = 'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-500/30 hover:text-brand-600 dark:hover:text-brand-accent transition';
?>
<section class="space-y-8">
    <div class="ux-hero ux-card p-6 md:p-8 lg:p-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 relative z-10">
            <div class="max-w-3xl space-y-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent border border-brand-500/20">
                    <i class="fa-solid fa-chalkboard-user text-[10px]"></i>
                    <?= escape(__('courses.instructor.badge')) ?>
                </span>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight">
                    <?= escape(__('courses.instructor.title')) ?>
                </h1>
                <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                    <?= escape(__('courses.instructor.subtitle')) ?>
                </p>
            </div>
            <a href="<?= escape(url('/instructor/courses/create')) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-plus"></i>
                <?= escape(__('courses.instructor.create')) ?>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6">
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('courses.instructor.stats.total_courses')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) $totalCourses) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-sky-500/10 text-sky-600 dark:text-sky-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('courses.instructor.stats.published')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $publishedCount) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-violet-500/10 text-violet-600 dark:text-violet-400">
                <i class="fa-solid fa-users"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('courses.instructor.stats.total_learners')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $totalEnrolled) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6 <?= $totalUnread > 0 ? 'border-red-500/20 bg-gradient-to-br from-red-500/8 to-transparent' : '' ?>">
            <div class="ux-stat-icon bg-red-500/10 text-red-600 dark:text-red-400">
                <i class="fa-solid fa-comment-dots"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('courses.instructor.stats.unread_messages')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold <?= $totalUnread > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' ?> mt-1"><?= escape((string) $totalUnread) ?></p>
        </div>
    </div>

    <div class="ux-card p-6 md:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-brand-500"></i>
                    <?= escape(__('courses.instructor.list_title')) ?>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.instructor.list_subtitle')) ?></p>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('courses.instructor.success.' . $success)) ?>
            </div>
        <?php endif; ?>

        <?php if ($courses === []): ?>
            <div class="flex flex-col items-center justify-center text-center px-4 py-14 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent mb-4">
                    <i class="fa-solid fa-book-medical text-xl"></i>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm"><?= escape(__('courses.instructor.empty_list')) ?></p>
                <a href="<?= escape(url('/instructor/courses/create')) ?>" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-brand-500 text-slate-950 font-bold text-sm hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-plus"></i>
                    <?= escape(__('courses.instructor.create')) ?>
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($courses as $course): ?>
                    <?php
                        $enrolled = (int) ($enrollmentCounts[$course->id] ?? 0);
                        $unreadCount = (int) ($unreadCounts[$course->id] ?? 0);
                        $editUrl = url('/instructor/courses/' . $course->id . '/edit');
                    ?>
                    <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40 overflow-hidden transition hover:border-brand-500/25 hover:shadow-md hover:shadow-brand-500/5">
                        <div class="p-5 md:p-6 flex flex-col xl:flex-row xl:items-start gap-5">
                            <div class="flex-1 min-w-0 space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full border <?= escape($statusClass($course->status)) ?>">
                                        <?= escape(__('courses.status.' . $course->status)) ?>
                                    </span>
                                    <?php if ($unreadCount > 0): ?>
                                        <a
                                            href="<?= escape($editUrl) ?>"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500 text-white text-xs font-bold shadow-sm shadow-red-500/30 hover:bg-red-600 transition"
                                            title="<?= escape(__('discussion.unread_badge', ['count' => (string) $unreadCount])) ?>"
                                        >
                                            <i class="fa-solid fa-comment-dots"></i>
                                            <?= escape((string) $unreadCount) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white leading-snug">
                                        <?= escape($course->title) ?>
                                    </h3>
                                    <?php if ($course->description): ?>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 line-clamp-2 leading-relaxed">
                                            <?= escape($course->description) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 dark:text-brand-accent">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-500/10">
                                        <i class="fa-solid fa-users text-xs"></i>
                                    </span>
                                    <span><?= escape((string) $enrolled) ?></span>
                                    <span class="text-slate-500 dark:text-slate-400 font-medium"><?= escape(__('courses.instructor.progress_stats.enrolled')) ?></span>
                                </div>
                            </div>

                            <div class="xl:w-[420px] shrink-0">
                                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold mb-3">
                                    <?= escape(__('courses.table.actions')) ?>
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/progress')) ?>" class="<?= escape($actionBtnClass) ?>">
                                        <i class="fa-solid fa-chart-line text-brand-500"></i>
                                        <?= escape(__('courses.instructor.view_progress')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/knowledge-items')) ?>" class="<?= escape($actionBtnClass) ?>">
                                        <i class="fa-solid fa-lightbulb text-amber-500"></i>
                                        <?= escape(__('knowledge_items.instructor.manage')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts')) ?>" class="<?= escape($actionBtnClass) ?>">
                                        <i class="fa-solid fa-user-group text-sky-500"></i>
                                        <?= escape(__('cohorts.instructor.manage')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="<?= escape($actionBtnClass) ?>">
                                        <i class="fa-solid fa-pen-to-square text-violet-500"></i>
                                        <?= escape(__('courses.instructor.edit')) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
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
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
