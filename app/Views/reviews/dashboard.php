<?php

$panel = $panel ?? [
    'today' => '',
    'due_today' => 0,
    'total_concepts' => 0,
    'reviewed_today' => 0,
    'due_items' => [],
    'upcoming_items' => [],
    'recent_items' => [],
];
$dueToday = (int) ($panel['due_today'] ?? 0);
$reviewedToday = (int) ($panel['reviewed_today'] ?? 0);
$totalConcepts = (int) ($panel['total_concepts'] ?? 0);
$todayLabel = date('d/m/Y', strtotime((string) ($panel['today'] ?? 'now')));
$dueItems = $panel['due_items'] ?? [];
$upcomingItems = $panel['upcoming_items'] ?? [];
$recentItems = $panel['recent_items'] ?? [];

ob_start();
?>
<section class="space-y-8">
    <div class="ux-hero ux-card p-6 md:p-8 lg:p-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 relative z-10">
            <div class="max-w-3xl space-y-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent border border-brand-500/20">
                    <i class="fa-solid fa-rotate text-[10px]"></i>
                    <?= escape(__('reviews.daily_title')) ?>
                </span>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight">
                    <?= escape(__('reviews.dashboard_title')) ?>
                </h1>
                <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                    <?= escape(__('reviews.dashboard_subtitle')) ?>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <?php if ($dueToday > 0): ?>
                    <a href="<?= escape(url('/review/daily')) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                        <i class="fa-solid fa-play"></i>
                        <?= escape(__('reviews.dashboard_cta_button')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= escape(url('/dashboard')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <?= escape(__('reviews.back_dashboard')) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6">
        <div class="ux-stat-card ux-card p-5 md:p-6 <?= $dueToday > 0 ? 'border-brand-500/25 bg-gradient-to-br from-brand-500/8 to-transparent' : '' ?>">
            <div class="ux-stat-icon bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-bell"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('reviews.dashboard_stats.due_today')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) $dueToday) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-sky-500/10 text-sky-600 dark:text-sky-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('reviews.dashboard_stats.reviewed_today')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $reviewedToday) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-violet-500/10 text-violet-600 dark:text-violet-400">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('reviews.dashboard_stats.total_concepts')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $totalConcepts) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('reviews.dashboard_stats.today')) ?></p>
            <p class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white mt-1"><?= escape($todayLabel) ?></p>
        </div>
    </div>

    <?php if ($dueToday > 0): ?>
        <div class="ux-card border-brand-500/25 bg-gradient-to-r from-brand-500/10 to-brand-500/5 p-5 md:p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-2 max-w-2xl">
                <p class="text-base md:text-lg font-bold text-brand-700 dark:text-brand-accent"><?= escape(__('reviews.dashboard_cta_title')) ?></p>
                <p class="text-sm text-brand-700/80 dark:text-brand-accent/80">
                    <?= escape(__('reviews.dashboard_cta_hint', ['count' => (string) $dueToday])) ?>
                </p>
            </div>
            <a href="<?= escape(url('/review/daily')) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-play"></i>
                <?= escape(__('reviews.dashboard_cta_button')) ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
        <div class="ux-card p-6 md:p-8 flex flex-col min-h-[360px]">
            <div class="flex items-start justify-between gap-4 mb-6">
                <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                        <i class="fa-solid fa-list-check text-sm"></i>
                    </span>
                    <?= escape(__('reviews.dashboard_due_title')) ?>
                </h2>
            </div>
            <?php if ($dueItems === []): ?>
                <div class="flex-1 flex flex-col items-center justify-center text-center px-4 py-10 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent mb-4">
                        <i class="fa-solid fa-inbox text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs"><?= escape(__('reviews.dashboard_due_empty')) ?></p>
                </div>
            <?php else: ?>
                <ul class="space-y-3 flex-1">
                    <?php foreach ($dueItems as $item): ?>
                        <li class="ux-list-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-4 md:p-5">
                            <p class="text-sm md:text-base font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                            <p class="text-[11px] md:text-xs text-slate-400 mt-3">
                                <?= escape(__('reviews.dashboard_item_meta', [
                                    'interval' => (string) $item['interval_days'],
                                    'next' => date('d/m/Y', strtotime((string) $item['next_review_date'])),
                                ])) ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="ux-card p-6 md:p-8 flex flex-col min-h-[360px]">
            <div class="flex items-start justify-between gap-4 mb-6">
                <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        <i class="fa-solid fa-calendar-days text-sm"></i>
                    </span>
                    <?= escape(__('reviews.dashboard_upcoming_title')) ?>
                </h2>
            </div>
            <?php if ($upcomingItems === []): ?>
                <div class="flex-1 flex flex-col items-center justify-center text-center px-4 py-10 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 mb-4">
                        <i class="fa-solid fa-calendar-plus text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs"><?= escape(__('reviews.dashboard_upcoming_empty')) ?></p>
                </div>
            <?php else: ?>
                <ul class="space-y-3 flex-1">
                    <?php foreach ($upcomingItems as $item): ?>
                        <li class="ux-list-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-4 md:p-5">
                            <p class="text-sm md:text-base font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                            <p class="text-[11px] md:text-xs text-slate-400 mt-3">
                                <?= escape(__('reviews.dashboard_next_on', [
                                    'date' => date('d/m/Y', strtotime((string) $item['next_review_date'])),
                                ])) ?>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="ux-card p-6 md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400">
                    <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                </span>
                <?= escape(__('reviews.dashboard_recent_title')) ?>
            </h2>
        </div>
        <?php if ($recentItems === []): ?>
            <div class="flex flex-col items-center justify-center text-center px-4 py-14 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-violet-500/10 text-violet-600 dark:text-violet-400 mb-4">
                    <i class="fa-solid fa-history text-xl"></i>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm"><?= escape(__('reviews.dashboard_recent_empty')) ?></p>
            </div>
        <?php else: ?>
            <ul class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                <?php foreach ($recentItems as $item): ?>
                    <li class="ux-list-item rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-4 md:p-5 flex flex-col justify-between gap-3 min-h-[120px]">
                        <div>
                            <p class="text-sm md:text-base font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                        </div>
                        <?php if (!empty($item['last_reviewed_at'])): ?>
                            <time class="text-[11px] md:text-xs text-slate-400 flex items-center gap-1.5" datetime="<?= escape((string) $item['last_reviewed_at']) ?>">
                                <i class="fa-regular fa-clock"></i>
                                <?= escape(date('d/m/Y H:i', strtotime((string) $item['last_reviewed_at']))) ?>
                            </time>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'reviews';
require base_path('app/Views/layouts/app.php');
