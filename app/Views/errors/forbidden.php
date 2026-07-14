<?php

ob_start();
?>
<div class="flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md text-center">
        <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900 mb-2"><?= escape(__('errors.access_denied')) ?></h1>
            <p class="text-slate-500 mb-6"><?= escape(__('errors.access_denied_message')) ?></p>
            <a href="<?= escape(url('/dashboard')) ?>" class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                <?= escape(__('errors.back_dashboard')) ?>
            </a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
