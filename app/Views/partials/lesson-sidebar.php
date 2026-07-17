<?php
/** @var array<string, mixed> $lessonNav */
/** @var \App\Models\Module $module */
$lessons = $lessonNav['lessons'] ?? [];
$resources = $lessonNav['resources'] ?? [];
$overallProgress = (int) ($lessonNav['overall_progress'] ?? 0);
$lessonCount = (int) ($lessonNav['lesson_count'] ?? count($lessons));
?>
<aside class="lesson-sidebar space-y-4">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('lesson.sidebar_title')) ?></h2>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-brand-500/15 text-brand-700 dark:text-brand-accent border border-brand-500/20">
                <?= escape(__('lesson.overall_progress', ['percent' => (string) $overallProgress])) ?>
            </span>
        </div>

        <?php if ($lessons === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('lesson.no_lessons')) ?></p>
        <?php else: ?>
            <ol class="space-y-2">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <?php
                    $state = (string) ($lesson['state'] ?? 'locked');
                    $quiz = $lesson['quiz'] ?? null;
                    $quizUrl = $lesson['quiz_url'] ?? null;
                    $quizState = (string) ($lesson['quiz_state'] ?? 'not_started');
                    $quizOnly = !empty($lesson['quiz_only']);
                    $lessonLocked = $state === 'locked';
                    $quizLocked = $lessonLocked;
                    $quizInteractive = !$quizLocked && $quizUrl !== null;
                    ?>
                    <?php if ($quizOnly): ?>
                        <?php
                        $quizIconClass = match ($quizState) {
                            'passed' => 'fa-circle-check text-brand-500',
                            'failed' => 'fa-rotate-right text-amber-500',
                            default => 'fa-clipboard-question text-brand-500',
                        };
                        $quizItemClass = $quizLocked
                            ? 'border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-950/40 opacity-70'
                            : ($quizState === 'passed'
                                ? 'border-brand-500/30 bg-brand-500/5 hover:border-brand-500/40'
                                : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-brand-500/30');
                        ?>
                        <li class="space-y-2">
                            <?php if ($quizInteractive): ?>
                                <a href="<?= escape((string) $quizUrl) ?>" class="lesson-sidebar-item flex items-center gap-3 p-3 rounded-xl border transition <?= escape($quizItemClass) ?>">
                                    <span class="w-6 text-center shrink-0">
                                        <i class="fa-solid <?= escape($quizIconClass) ?>"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200"><?= escape($quiz->title) ?></span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.sidebar_quiz_label')) ?></span>
                                    </span>
                                </a>
                            <?php else: ?>
                                <div class="flex items-center gap-3 p-3 rounded-xl border <?= escape($quizItemClass) ?>">
                                    <span class="w-6 text-center shrink-0">
                                        <i class="fa-solid fa-lock text-amber-500"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200"><?= escape($quiz->title) ?></span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.sidebar_quiz_locked')) ?></span>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php
                    $quizLocked = $lessonLocked;
                    $quizInteractive = !$quizLocked && $quizUrl !== null;
                    $lessonInteractive = !$lessonLocked;
                    $itemClass = match ($state) {
                        'current' => 'border-brand-500/40 bg-brand-500/10',
                        'completed' => 'border-brand-500/35 bg-brand-500/10',
                        'available' => 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-brand-500/30',
                        default => 'border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-950/40 opacity-70',
                    };
                    $iconClass = match ($state) {
                        'completed' => 'fa-circle-check text-brand-500',
                        'current' => 'fa-circle-play text-sky-500',
                        'available' => 'fa-circle-play text-brand-500',
                        default => 'fa-lock text-amber-500',
                    };
                    $quizIconClass = match ($quizState) {
                        'passed' => 'fa-circle-check text-brand-500',
                        'failed' => 'fa-rotate-right text-amber-500',
                        default => 'fa-clipboard-question text-brand-500',
                    };
                    ?>
                    <li class="space-y-2">
                        <?php if ($lessonInteractive): ?>
                            <a
                                href="<?= escape((string) ($lesson['url'] ?? '#')) ?>"
                                class="lesson-sidebar-item flex items-start gap-3 p-3 rounded-xl border transition <?= escape($itemClass) ?>"
                            >
                                <span class="mt-0.5 w-6 text-center shrink-0">
                                    <i class="fa-solid <?= escape($iconClass) ?>"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white leading-snug">
                                        <?= escape((string) ($index + 1)) ?>. <?= escape($lesson['nugget']->title) ?>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        <?= escape((string) ($lesson['duration_label'] ?? '')) ?>
                                    </span>
                                </span>
                            </a>
                        <?php else: ?>
                            <div class="lesson-sidebar-item flex items-start gap-3 p-3 rounded-xl border <?= escape($itemClass) ?>">
                                <span class="mt-0.5 w-6 text-center shrink-0">
                                    <i class="fa-solid <?= escape($iconClass) ?>"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-white leading-snug">
                                        <?= escape((string) ($index + 1)) ?>. <?= escape($lesson['nugget']->title) ?>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        <?= escape((string) ($lesson['duration_label'] ?? '')) ?>
                                    </span>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($quiz !== null && $quizUrl !== null): ?>
                            <?php
                            $quizItemClass = $quizLocked
                                ? 'border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-950/40 opacity-70'
                                : ($quizState === 'passed'
                                    ? 'border-brand-500/30 bg-brand-500/5 hover:border-brand-500/40'
                                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-brand-500/30');
                            ?>
                            <?php if ($quizInteractive): ?>
                                <a href="<?= escape((string) $quizUrl) ?>" class="lesson-sidebar-item flex items-center gap-3 p-3 ml-4 rounded-xl border transition <?= escape($quizItemClass) ?>">
                                    <span class="w-6 text-center shrink-0">
                                        <i class="fa-solid <?= escape($quizIconClass) ?>"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200"><?= escape($quiz->title) ?></span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.sidebar_quiz_label')) ?></span>
                                    </span>
                                </a>
                            <?php else: ?>
                                <div class="flex items-center gap-3 p-3 ml-4 rounded-xl border <?= escape($quizItemClass) ?>">
                                    <span class="w-6 text-center shrink-0">
                                        <i class="fa-solid fa-lock text-amber-500"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200"><?= escape($quiz->title) ?></span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('lesson.sidebar_quiz_locked')) ?></span>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <?php if ($resources !== []): ?>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                <i class="fa-solid fa-file-pdf text-brand-500"></i>
                <?= escape(__('lesson.resources_title')) ?>
            </h3>
            <ul class="space-y-2">
                <?php foreach ($resources as $resource): ?>
                    <li>
                        <a
                            href="<?= escape((string) $resource['url']) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500/30 transition text-sm"
                        >
                            <span class="font-medium text-slate-800 dark:text-slate-200"><?= escape((string) $resource['title']) ?></span>
                            <i class="fa-solid fa-download text-brand-500"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($discussionModuleId)): ?>
        <?php require base_path('app/Views/partials/lesson-discussion-board.php'); ?>
    <?php endif; ?>
</aside>
