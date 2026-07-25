<?php
/** @var string $heroTitle */
/** @var string $heroSubtitle */
/** @var string|null $heroBadge */
/** @var string $heroBadgeIcon */
$heroBadge = $heroBadge ?? __('courses.instructor.badge');
$heroBadgeIcon = $heroBadgeIcon ?? 'fa-chalkboard-user';
$heroActions = $heroActions ?? '';
?>
<div class="ux-hero ux-card p-6 md:p-8 lg:p-10">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 relative z-10">
        <div class="max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent border border-brand-500/20">
                <i class="fa-solid <?= escape($heroBadgeIcon) ?> text-[10px]"></i>
                <?= escape($heroBadge) ?>
            </span>
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight">
                <?= escape($heroTitle) ?>
            </h1>
            <?php if ($heroSubtitle !== ''): ?>
                <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                    <?= escape($heroSubtitle) ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if ($heroActions !== ''): ?>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <?= $heroActions ?>
            </div>
        <?php endif; ?>
    </div>
</div>
