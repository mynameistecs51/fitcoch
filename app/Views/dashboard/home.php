<?php

ob_start();
?>
<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900"><?= escape(__('dashboard.welcome', ['name' => $user->firstName])) ?></h1>
                <p class="text-slate-500 mt-1"><?= escape(__('dashboard.signed_in')) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?php if (!empty($isAdmin)): ?>
                    <a href="<?= escape(url('/admin/users')) ?>" class="px-4 py-2 text-sm border border-indigo-200 text-indigo-700 rounded-lg hover:bg-indigo-50 transition-colors">
                        <?= escape(__('admin.title')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= escape(url('/profile')) ?>" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <?= escape(__('nav.profile')) ?>
                </a>
                <form method="POST" action="<?= escape(url('/logout')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button type="submit" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        <?= escape(__('nav.sign_out')) ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500"><?= escape(__('dashboard.email')) ?></p>
                <p class="font-semibold mt-1"><?= escape($user->email) ?></p>
            </div>
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500"><?= escape(__('dashboard.timezone')) ?></p>
                <p class="font-semibold mt-1"><?= escape($user->timezone) ?></p>
            </div>
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500"><?= escape(__('dashboard.roles')) ?></p>
                <p class="font-semibold mt-1"><?= escape(translate_roles($roles)) ?></p>
            </div>
        </div>

        <div class="mt-8 p-6 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800">
            <p class="font-semibold"><?= escape(__('dashboard.sprint_title')) ?></p>
            <p class="text-sm mt-1"><?= escape(__('dashboard.sprint_message')) ?></p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
