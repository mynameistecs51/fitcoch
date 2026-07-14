<?php

ob_start();
?>
<div class="flex items-center justify-center px-6 py-16 min-h-[calc(100vh-8rem)]">
    <div class="w-full max-w-md text-center">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl">
            <div class="w-16 h-16 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2"><?= escape(__('errors.access_denied')) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mb-6 text-sm"><?= escape(__('errors.access_denied_message')) ?></p>
            <a href="<?= escape(url('/dashboard')) ?>" class="inline-block px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                <?= escape(__('errors.back_dashboard')) ?>
            </a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = false;
require base_path('app/Views/layouts/app.php');
