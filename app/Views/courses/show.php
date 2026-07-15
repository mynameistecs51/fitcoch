<?php

ob_start();
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$nuggetsByModule = $nuggetsByModule ?? [];
$quizzesByModule = $quizzesByModule ?? [];
$sessionsByModule = $sessionsByModule ?? [];
$ticketsByModule = $ticketsByModule ?? [];
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape($course->title) ?></h1>
            <?php if ($course->description): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl"><?= escape($course->description) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= escape(url('/courses')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('courses.back_to_list')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center">
            <i class="fa-solid fa-list-ol text-brand-500 mr-2"></i>
            <?= escape(__('courses.syllabus_title')) ?>
        </h2>

        <?php if ($modules === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
        <?php else: ?>
            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?> w-16"><?= escape(__('courses.table.order')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.module')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.content')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($modules as $module): ?>
                            <?php $moduleNuggets = $nuggetsByModule[$module->id] ?? []; ?>
                            <?php $moduleQuiz = $quizzesByModule[$module->id] ?? null; ?>
                            <?php $moduleTicket = $ticketsByModule[$module->id] ?? null; ?>
                            <?php $moduleSessions = $sessionsByModule[$module->id] ?? []; ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent">
                                    <?= escape((string) $module->sequenceOrder) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                    <?= escape($module->title) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-slate-500 dark:text-slate-400 text-xs">
                                    <?php if ($moduleNuggets === [] && $moduleQuiz === null && $moduleSessions === []): ?>
                                        <?= escape(__('courses.no_nuggets')) ?>
                                    <?php else: ?>
                                        <ul class="space-y-1">
                                            <?php foreach ($moduleNuggets as $nugget): ?>
                                                <li>
                                                    <a href="<?= escape(url('/nuggets/' . $nugget->id)) ?>" class="hover:text-brand-600 dark:hover:text-brand-500 transition">
                                                        <i class="fa-solid fa-circle-play text-brand-500 mr-1"></i>
                                                        <?= escape($nugget->title) ?>
                                                    </a>
                                                    <?php if ($nugget->nuggetType === 'video'): ?>
                                                        — <?= escape(__('courses.nugget_video')) ?>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if ($moduleQuiz !== null): ?>
                                                <li>
                                                    <a href="<?= escape(url('/quizzes/' . $moduleQuiz->id)) ?>" class="hover:text-brand-600 dark:hover:text-brand-500 transition font-semibold">
                                                        <i class="fa-solid fa-clipboard-question text-brand-500 mr-1"></i>
                                                        <?= escape($moduleQuiz->title) ?>
                                                    </a>
                                                    <?php if ($moduleTicket !== null): ?>
                                                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold <?= $moduleTicket->isOpen() ? 'bg-brand-500/15 text-brand-700 dark:text-brand-accent' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
                                                            <?= escape(__('quizzes.ticket_status.' . $moduleTicket->status)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php foreach ($moduleSessions as $liveSession): ?>
                                                <?php if (!$liveSession->isJoinable()) { continue; } ?>
                                                <li>
                                                    <?php if ($moduleTicket !== null && $moduleTicket->isOpen()): ?>
                                                        <a href="<?= escape(url('/live/' . $liveSession->id)) ?>" class="hover:text-brand-600 dark:hover:text-brand-500 transition font-semibold">
                                                            <i class="fa-solid fa-video text-brand-500 mr-1"></i>
                                                            <?= escape($liveSession->title) ?>
                                                        </a>
                                                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-500/15 text-brand-700 dark:text-brand-accent">
                                                            <?= escape(__('live.status.' . $liveSession->status)) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-slate-400">
                                                            <i class="fa-solid fa-video mr-1"></i>
                                                            <?= escape($liveSession->title) ?>
                                                            — <?= escape(__('live.syllabus_locked')) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
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
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
