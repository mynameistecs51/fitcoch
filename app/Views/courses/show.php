<?php

ob_start();
$nuggetsByModule = $nuggetsByModule ?? [];
$quizzesByModule = $quizzesByModule ?? [];
$sessionsByModule = $sessionsByModule ?? [];
$ticketsByModule = $ticketsByModule ?? [];
$lessonNav = $lessonNav ?? null;
$resumeLessonUrl = $resumeLessonUrl ?? null;
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

                <?php if ($modules === []): ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($modules as $module): ?>
                            <?php
                            $moduleNuggets = $nuggetsByModule[$module->id] ?? [];
                            $moduleQuiz = $quizzesByModule[$module->id] ?? null;
                            $moduleTicket = $ticketsByModule[$module->id] ?? null;
                            $moduleSessions = $sessionsByModule[$module->id] ?? [];
                            $hasContent = $moduleNuggets !== [] || $moduleQuiz !== null || $moduleSessions !== [];
                            ?>
                            <article class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-500/15 text-brand-700 dark:text-brand-accent text-sm font-bold shrink-0">
                                        <?= escape((string) $module->sequenceOrder) ?>
                                    </span>
                                    <h3 class="text-sm md:text-base font-semibold text-slate-900 dark:text-white"><?= escape($module->title) ?></h3>
                                </div>

                                <div class="p-4">
                                    <?php if (!$hasContent): ?>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                            <i class="fa-regular fa-folder-open"></i>
                                            <?= escape(__('courses.no_nuggets')) ?>
                                        </p>
                                    <?php else: ?>
                                        <ul class="space-y-2">
                                            <?php foreach ($moduleNuggets as $nugget): ?>
                                                <li>
                                                    <a
                                                        href="<?= escape(url('/nuggets/' . $nugget->id)) ?>"
                                                        class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500/40 hover:bg-brand-500/5 transition"
                                                    >
                                                        <span class="w-9 h-9 rounded-full bg-brand-500/15 flex items-center justify-center shrink-0">
                                                            <i class="fa-solid fa-circle-play text-brand-500"></i>
                                                        </span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block text-sm font-semibold text-slate-900 dark:text-white"><?= escape($nugget->title) ?></span>
                                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('courses.nugget_video')) ?></span>
                                                        </span>
                                                        <i class="fa-solid fa-chevron-right text-slate-400 text-xs"></i>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>

                                            <?php if ($moduleQuiz !== null): ?>
                                                <li>
                                                    <a
                                                        href="<?= escape(url('/quizzes/' . $moduleQuiz->id)) ?>"
                                                        class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500/40 hover:bg-brand-500/5 transition"
                                                    >
                                                        <span class="w-9 h-9 rounded-full bg-brand-500/15 flex items-center justify-center shrink-0">
                                                            <i class="fa-solid fa-clipboard-question text-brand-500"></i>
                                                        </span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block text-sm font-semibold text-slate-900 dark:text-white"><?= escape($moduleQuiz->title) ?></span>
                                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.pretest_title')) ?></span>
                                                        </span>
                                                        <?php if ($moduleTicket !== null): ?>
                                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold <?= $moduleTicket->isOpen() ? 'bg-brand-500/15 text-brand-700 dark:text-brand-accent' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
                                                                <?= escape(__('quizzes.ticket_status.' . $moduleTicket->status)) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php foreach ($moduleSessions as $liveSession): ?>
                                                <?php if (!$liveSession->isJoinable()) { continue; } ?>
                                                <li>
                                                    <?php if ($moduleTicket !== null && $moduleTicket->isOpen()): ?>
                                                        <a
                                                            href="<?= escape(url('/live/' . $liveSession->id)) ?>"
                                                            class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500/40 transition"
                                                        >
                                                            <span class="w-9 h-9 rounded-full bg-brand-500/15 flex items-center justify-center shrink-0">
                                                                <i class="fa-solid fa-video text-brand-500"></i>
                                                            </span>
                                                            <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape($liveSession->title) ?></span>
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-slate-400 text-sm">
                                                            <i class="fa-solid fa-video"></i>
                                                            <span><?= escape($liveSession->title) ?> — <?= escape(__('live.syllabus_locked')) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
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
