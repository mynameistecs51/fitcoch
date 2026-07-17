<?php

ob_start();
$nuggetsByModule = $nuggetsByModule ?? [];
$quizzesByModule = $quizzesByModule ?? [];
$ticketsByModule = $ticketsByModule ?? [];
$lessonNav = $lessonNav ?? null;
$resumeLessonUrl = $resumeLessonUrl ?? null;
$syllabusSummary = $syllabusSummary ?? null;
?>
<section class="lesson-page max-w-[1400px] mx-auto space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-500 mb-2">
                <?= escape($course->title) ?>
            </p>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">
                <?= escape(__('courses.subtitle')) ?>
            </h1>
            <?php if ($course->description): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-3xl"><?= escape($course->description) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-3xl"><?= escape(__('lesson.portal_description')) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= escape(url('/courses')) ?>" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent shrink-0">
            <i class="fa-solid fa-arrow-left"></i>
            <?= escape(__('courses.back_to_list')) ?>
        </a>
    </div>

    <?php if ($resumeLessonUrl !== null): ?>
        <div class="rounded-2xl border border-brand-500/30 bg-gradient-to-r from-brand-500/10 via-brand-500/5 to-transparent p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('lesson.continue_title')) ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('lesson.continue_hint')) ?></p>
            </div>
            <a
                href="<?= escape($resumeLessonUrl) ?>"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition shadow-lg shadow-brand-500/20 shrink-0"
            >
                <i class="fa-solid fa-circle-play"></i>
                <?= escape(__('lesson.start_learning')) ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="lesson-grid">
        <div class="lesson-main min-w-0">
            <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 md:p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-brand-500"></i>
                    <?= escape(__('courses.syllabus_title')) ?>
                </h2>

                <?php if ($syllabusSummary !== null): ?>
                    <?php
                        $syllabus = $syllabusSummary;
                        $compact = false;
                        require base_path('app/Views/partials/course-lesson-structure.php');
                    ?>
                <?php elseif ($modules === []): ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
                <?php else: ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_nuggets')) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($lessonNav !== null): ?>
            <?php require base_path('app/Views/partials/lesson-sidebar.php'); ?>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
