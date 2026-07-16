<?php

ob_start();
$learnerName = trim($learner->firstName . ' ' . $learner->lastName);
$awardedDate = date('d M Y', strtotime($certificate->awardedAt));
?>
<section class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape(__('certificates.title')) ?></h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('certificates.verified')) ?></p>
        </div>
        <?php if (!empty($showSidebar)): ?>
            <a href="<?= escape(url('/dashboard')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                <?= escape(__('errors.back_dashboard')) ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="max-w-4xl mx-auto border-8 border-amber-500/25 p-6 md:p-8 bg-white dark:bg-slate-900 rounded-3xl relative overflow-hidden shadow-2xl print:border-amber-500 print:shadow-none print:bg-white">
        <div class="absolute inset-0 opacity-5 pointer-events-none flex items-center justify-center">
            <i class="fa-solid fa-award text-[12rem] text-amber-500"></i>
        </div>
        <div class="border-2 border-amber-500/20 p-6 md:p-10 rounded-2xl flex flex-col items-center text-center relative">
            <p class="text-xs uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400 font-bold"><?= escape(__('certificates.mastery_label')) ?></p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mt-4"><?= escape(__('certificates.awarded_to')) ?></h2>
            <p class="text-2xl md:text-3xl font-bold text-brand-600 dark:text-brand-accent mt-3"><?= escape($learnerName) ?></p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-6 max-w-xl"><?= escape(__('certificates.completion_text')) ?></p>
            <p class="text-xl md:text-2xl font-semibold text-slate-900 dark:text-white mt-2"><?= escape($course->title) ?></p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-6"><?= escape(__('certificates.awarded_on', ['date' => $awardedDate])) ?></p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-4 font-mono"><?= escape(__('certificates.verify_code', ['hash' => $certificate->verificationHash])) ?></p>

            <?php if ($badges !== []): ?>
                <div class="flex flex-wrap justify-center gap-2 mt-6">
                    <?php foreach ($badges as $badgeRow): ?>
                        <?php $badge = $badgeRow['badge'] ?? []; ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs font-semibold text-amber-700 dark:text-amber-300">
                            <i class="fa-solid <?= escape((string) ($badge['icon_url'] ?? 'fa-award')) ?>"></i>
                            <?= escape(__('gamification.badges.' . ($badge['name'] ?? 'unknown'))) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 print:hidden">
        <a href="<?= escape((string) $downloadUrl) ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
            <i class="fa-solid fa-file-pdf"></i>
            <?= escape(__('certificates.download_pdf')) ?>
        </a>
        <button type="button" id="copy-verify-link" data-url="<?= escape((string) $verificationUrl) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            <i class="fa-solid fa-link"></i>
            <?= escape(__('certificates.copy_link')) ?>
        </button>
    </div>
</section>
<script>
document.getElementById('copy-verify-link')?.addEventListener('click', function () {
    const url = this.dataset.url || '';
    if (!url || !navigator.clipboard) return;
    navigator.clipboard.writeText(url);
});
</script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = $showSidebar ?? false;
$currentNav = 'dashboard';
require base_path('app/Views/layouts/app.php');
