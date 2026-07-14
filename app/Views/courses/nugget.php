<?php
$progressPercent = (int) ($progress['progress_percentage'] ?? 0);
$progressStatus = (string) ($progress['status'] ?? 'in_progress');

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-xs text-brand-600 dark:text-brand-500 font-semibold mb-1">
                <?= escape($course->title) ?> · <?= escape($module->title) ?>
            </p>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape($nugget->title) ?></h1>
        </div>
        <a href="<?= escape(url('/courses/' . $course->id)) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('nuggets.back_to_syllabus')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
        <div class="aspect-video rounded-2xl overflow-hidden bg-slate-950 border border-slate-200 dark:border-slate-800">
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
                <div class="w-full h-full flex items-center justify-center text-sm text-slate-400">
                    <?= escape(__('nuggets.no_video_source')) ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">
                <span><?= escape(__('nuggets.progress_label')) ?></span>
                <span id="nugget-progress-text"><?= escape((string) $progressPercent) ?>%</span>
            </div>
            <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
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
