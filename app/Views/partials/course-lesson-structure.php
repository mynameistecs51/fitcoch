<?php
/** @var array<string, mixed> $syllabus */
/** @var bool $compact */
$syllabus = $syllabus ?? [];
$compact = $compact ?? false;
$modules = $syllabus['modules'] ?? [];
$lessonsByModule = $syllabus['lessons_by_module'] ?? [];
$overallProgress = (int) ($syllabus['overall_progress'] ?? 0);
?>
<?php if ($modules === []): ?>
    <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
<?php else: ?>
    <?php if (!$compact): ?>
        <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <?= escape(__('courses.syllabus_title')) ?>
            </p>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-brand-500/15 text-brand-700 dark:text-brand-accent border border-brand-500/20">
                <?= escape(__('lesson.overall_progress', ['percent' => (string) $overallProgress])) ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="space-y-3">
        <?php foreach ($modules as $module): ?>
            <?php $moduleLessons = $lessonsByModule[$module->id] ?? []; ?>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-500/15 text-brand-700 dark:text-brand-accent text-xs font-bold shrink-0">
                        <?= escape((string) $module->sequenceOrder) ?>
                    </span>
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white"><?= escape($module->title) ?></h4>
                </div>

                <?php if ($moduleLessons === []): ?>
                    <p class="px-3 py-3 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_nuggets')) ?></p>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($moduleLessons as $lesson): ?>
                            <?php
                                $state = (string) ($lesson['state'] ?? 'locked');
                                $quiz = $lesson['quiz'] ?? null;
                                $quizUrl = $lesson['quiz_url'] ?? null;
                                $quizState = (string) ($lesson['quiz_state'] ?? 'not_started');
                                $lessonLocked = $state === 'locked';
                                $quizOnly = !empty($lesson['quiz_only']);
                            ?>
                            <?php if ($quizOnly): ?>
                                <?php
                                    $quizPassed = $quizState === 'passed';
                                    $quizClass = $quizPassed
                                        ? 'bg-brand-500/10 border-l-4 border-l-brand-500'
                                        : ($lessonLocked ? 'opacity-60' : 'hover:bg-slate-50 dark:hover:bg-slate-950/50');
                                ?>
                                <li>
                                    <?php if ($lessonLocked || $quizUrl === null): ?>
                                        <div class="flex items-center gap-3 px-3 py-2.5 <?= escape($quizClass) ?>">
                                            <i class="fa-solid fa-lock text-amber-500 text-sm w-5 text-center shrink-0"></i>
                                            <span class="text-sm text-slate-600 dark:text-slate-300"><?= escape($quiz?->title ?? __('lesson.sidebar_quiz_label')) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= escape((string) $quizUrl) ?>" class="flex items-center gap-3 px-3 py-2.5 transition <?= escape($quizClass) ?>">
                                            <i class="fa-solid <?= $quizPassed ? 'fa-circle-check text-brand-500' : 'fa-clipboard-question text-brand-500' ?> text-sm w-5 text-center shrink-0"></i>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-sm font-medium <?= $quizPassed ? 'text-brand-700 dark:text-brand-accent' : 'text-slate-900 dark:text-white' ?>">
                                                    <?= escape($quiz->title) ?>
                                                </span>
                                                <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.sidebar_quiz_label')) ?></span>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                </li>
                                <?php continue; ?>
                            <?php endif; ?>
                            <?php
                                $videoCompleted = !empty($lesson['video_completed']);
                                $lessonLocked = $state === 'locked';
                                $videoClass = $videoCompleted
                                    ? 'bg-brand-500/10 border-l-4 border-l-brand-500'
                                    : ($lessonLocked ? 'opacity-60' : 'hover:bg-slate-50 dark:hover:bg-slate-950/50');
                            ?>
                            <li>
                                <?php if ($lessonLocked): ?>
                                    <div class="flex items-center gap-3 px-3 py-2.5 <?= escape($videoClass) ?>">
                                        <i class="fa-solid fa-lock text-amber-500 text-sm w-5 text-center shrink-0"></i>
                                        <span class="text-sm text-slate-600 dark:text-slate-300"><?= escape($lesson['nugget']->title) ?></span>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= escape((string) ($lesson['url'] ?? '#')) ?>" class="flex items-center gap-3 px-3 py-2.5 transition <?= escape($videoClass) ?>">
                                        <i class="fa-solid <?= $videoCompleted ? 'fa-circle-check text-brand-500' : 'fa-circle-play text-brand-500' ?> text-sm w-5 text-center shrink-0"></i>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-medium <?= $videoCompleted ? 'text-brand-700 dark:text-brand-accent' : 'text-slate-900 dark:text-white' ?>">
                                                <?= escape($lesson['nugget']->title) ?>
                                            </span>
                                            <?php if ($videoCompleted): ?>
                                                <span class="block text-[10px] font-semibold text-brand-600 dark:text-brand-accent mt-0.5">
                                                    <?= escape(__('courses.lesson_completed')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </a>
                                <?php endif; ?>

                                <?php if ($quiz !== null && $quizUrl !== null): ?>
                                    <?php
                                        $quizPassed = $quizState === 'passed';
                                        $quizLocked = $lessonLocked;
                                        $quizClass = $quizPassed
                                            ? 'bg-brand-500/10 border-l-4 border-l-brand-500'
                                            : ($quizLocked ? 'opacity-60' : 'hover:bg-slate-50 dark:hover:bg-slate-950/50');
                                    ?>
                                    <?php if ($quizLocked): ?>
                                        <div class="flex items-center gap-3 px-3 py-2 pl-8 <?= escape($quizClass) ?>">
                                            <i class="fa-solid fa-lock text-amber-500 text-xs w-4 text-center shrink-0"></i>
                                            <span class="text-xs text-slate-600 dark:text-slate-300"><?= escape($quiz->title) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= escape((string) $quizUrl) ?>" class="flex items-center gap-3 px-3 py-2 pl-8 transition <?= escape($quizClass) ?>">
                                            <i class="fa-solid <?= $quizPassed ? 'fa-circle-check text-brand-500' : 'fa-clipboard-question text-brand-500' ?> text-xs w-4 text-center shrink-0"></i>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-xs font-medium <?= $quizPassed ? 'text-brand-700 dark:text-brand-accent' : 'text-slate-800 dark:text-slate-200' ?>">
                                                    <?= escape($quiz->title) ?>
                                                </span>
                                                <?php if ($quizPassed): ?>
                                                    <span class="block text-[10px] font-semibold text-brand-600 dark:text-brand-accent mt-0.5">
                                                        <?= escape(__('courses.lesson_completed')) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
