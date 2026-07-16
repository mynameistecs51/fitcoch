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
$dueItems = $panel['due_items'] ?? [];
$upcomingItems = $panel['upcoming_items'] ?? [];
$recentItems = $panel['recent_items'] ?? [];

ob_start();
?>
<section class="space-y-6 max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-chart-pie text-brand-500"></i>
                <?= escape(__('reviews.dashboard_title')) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('reviews.dashboard_subtitle')) ?></p>
        </div>
        <a href="<?= escape(url('/dashboard')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('reviews.back_dashboard')) ?>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('reviews.dashboard_stats.due_today')) ?></p>
            <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-accent mt-2"><?= escape((string) $dueToday) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('reviews.dashboard_stats.reviewed_today')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2"><?= escape((string) ($panel['reviewed_today'] ?? 0)) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('reviews.dashboard_stats.total_concepts')) ?></p>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2"><?= escape((string) ($panel['total_concepts'] ?? 0)) ?></p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= escape(__('reviews.dashboard_stats.today')) ?></p>
            <p class="text-lg font-bold text-slate-900 dark:text-white mt-2"><?= escape(date('d/m/Y', strtotime((string) ($panel['today'] ?? 'now')))) ?></p>
        </div>
    </div>

    <?php if ($dueToday > 0): ?>
        <div class="rounded-2xl border border-brand-500/20 bg-brand-500/10 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-brand-700 dark:text-brand-accent"><?= escape(__('reviews.dashboard_cta_title')) ?></p>
                <p class="text-xs text-brand-700/80 dark:text-brand-accent/80 mt-1">
                    <?= escape(__('reviews.dashboard_cta_hint', ['count' => (string) $dueToday])) ?>
                </p>
            </div>
            <a href="<?= escape(url('/review/daily')) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20 shrink-0">
                <i class="fa-solid fa-play"></i>
                <?= escape(__('reviews.dashboard_cta_button')) ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-brand-500"></i>
                <?= escape(__('reviews.dashboard_due_title')) ?>
            </h2>
            <?php if ($dueItems === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                    <?= escape(__('reviews.dashboard_due_empty')) ?>
                </p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($dueItems as $item): ?>
                        <li class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-3">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                            <p class="text-[11px] text-slate-400 mt-2">
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

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-brand-500"></i>
                <?= escape(__('reviews.dashboard_upcoming_title')) ?>
            </h2>
            <?php if ($upcomingItems === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                    <?= escape(__('reviews.dashboard_upcoming_empty')) ?>
                </p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($upcomingItems as $item): ?>
                        <li class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-3">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                            <p class="text-[11px] text-slate-400 mt-2">
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

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-brand-500"></i>
            <?= escape(__('reviews.dashboard_recent_title')) ?>
        </h2>
        <?php if ($recentItems === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                <?= escape(__('reviews.dashboard_recent_empty')) ?>
            </p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach ($recentItems as $item): ?>
                    <li class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape((string) $item['concept_name']) ?></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape((string) $item['course_title']) ?></p>
                        </div>
                        <?php if (!empty($item['last_reviewed_at'])): ?>
                            <time class="text-[11px] text-slate-400 shrink-0" datetime="<?= escape((string) $item['last_reviewed_at']) ?>">
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
