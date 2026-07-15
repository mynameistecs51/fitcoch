<?php
$progressPercent = (int) ($progress['progress_percentage'] ?? 0);
$progressStatus = (string) ($progress['status'] ?? 'in_progress');
$lessonNav = $lessonNav ?? null;
$moduleQuiz = $moduleQuiz ?? null;
$moduleQuizQuestions = $moduleQuizQuestions ?? [];
$moduleQuizTicket = $moduleQuizTicket ?? null;
$moduleQuizResult = $moduleQuizResult ?? null;
$moduleQuizError = $moduleQuizError ?? null;

ob_start();
?>
<section class="lesson-page max-w-[1400px] mx-auto space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-500 mb-2">
                <?= escape($course->title) ?> · <?= escape($module->title) ?>
            </p>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">
                <?= escape(__('courses.subtitle')) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-3xl">
                <?= escape(__('lesson.portal_description')) ?>
            </p>
        </div>
        <a href="<?= escape(url('/courses/' . $course->id . '?view=syllabus')) ?>" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent shrink-0">
            <i class="fa-solid fa-arrow-left"></i>
            <?= escape(__('lesson.view_syllabus')) ?>
        </a>
    </div>

    <div class="lesson-grid">
        <div class="lesson-main space-y-5 min-w-0">
            <div class="lesson-video-card rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-brand-500/15 text-brand-700 dark:text-brand-accent">
                        <?= escape(__('lesson.unit_label', ['unit' => (string) $module->sequenceOrder])) ?>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate"><?= escape($nugget->title) ?></span>
                </div>

                <div class="aspect-video bg-slate-950 relative">
                    <?php if ($youtubeId): ?>
                        <iframe
                            id="nugget-youtube-player"
                            class="w-full h-full"
                            src="https://www.youtube.com/embed/<?= escape($youtubeId) ?>?rel=0"
                            title="<?= escape($nugget->title) ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    <?php elseif ($streamUrl): ?>
                        <video
                            id="nugget-video-player"
                            class="w-full h-full"
                            controls
                            playsinline
                            preload="metadata"
                            src="<?= escape($streamUrl) ?>"
                        ></video>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 gap-3">
                            <span class="w-16 h-16 rounded-full bg-brand-500/20 border border-brand-500/40 flex items-center justify-center">
                                <i class="fa-solid fa-circle-play text-3xl text-brand-500"></i>
                            </span>
                            <p class="text-sm"><?= escape(__('nuggets.no_video_source')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">
                        <span><?= escape(__('nuggets.progress_label')) ?></span>
                        <span id="nugget-progress-text"><?= escape((string) $progressPercent) ?>%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                        <div
                            id="nugget-progress-bar"
                            class="h-full rounded-full bg-gradient-to-r from-brand-600 via-brand-500 to-brand-accent transition-all duration-300"
                            style="width: <?= escape((string) max(0, min(100, $progressPercent))) ?>%"
                        ></div>
                    </div>
                    <p id="nugget-progress-status" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        <?= escape($progressStatus === 'completed' ? __('nuggets.status_completed') : __('nuggets.status_in_progress')) ?>
                    </p>
                </div>
            </div>

            <?php if ($moduleQuiz !== null && $moduleQuizQuestions !== []): ?>
                <?php
                $quiz = $moduleQuiz;
                $questions = $moduleQuizQuestions;
                $ticket = $moduleQuizTicket;
                $result = $moduleQuizResult;
                $error = $moduleQuizError;
                $compact = false;
                $retakeLessonUrl = url('/nuggets/' . $nugget->id);
                require base_path('app/Views/partials/lesson-quiz-panel.php');
                ?>
            <?php endif; ?>
        </div>

        <?php if ($lessonNav !== null): ?>
            <?php require base_path('app/Views/partials/lesson-sidebar.php'); ?>
        <?php endif; ?>
    </div>
</section>

<script>
window.FitCochNugget = {
    nuggetId: <?= (int) $nugget->id ?>,
    progressUrl: <?= json_encode(url('/api/v1/nuggets/' . $nugget->id . '/progress'), JSON_THROW_ON_ERROR) ?>,
    initialProgress: <?= (int) $progressPercent ?>,
    labels: {
        completed: <?= json_encode(__('nuggets.status_completed'), JSON_THROW_ON_ERROR) ?>,
        inProgress: <?= json_encode(__('nuggets.status_in_progress'), JSON_THROW_ON_ERROR) ?>,
    },
};
</script>
<script src="<?= escape(url('/assets/nugget-player.js')) ?>"></script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
