<?php

ob_start();
?>
<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Welcome, <?= escape($user->firstName) ?>!</h1>
                <p class="text-slate-500 mt-1">You are signed in to FitCoch.</p>
            </div>
            <form method="POST" action="/logout">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <button type="submit" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    Sign Out
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500">Email</p>
                <p class="font-semibold mt-1"><?= escape($user->email) ?></p>
            </div>
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500">Timezone</p>
                <p class="font-semibold mt-1"><?= escape($user->timezone) ?></p>
            </div>
            <div class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-500">Status</p>
                <p class="font-semibold mt-1 capitalize"><?= escape($user->status) ?></p>
            </div>
        </div>

        <div class="mt-8 p-6 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800">
            <p class="font-semibold">Sprint 1 Complete — Authentication Core</p>
            <p class="text-sm mt-1">Course content, quizzes, and gamification features will arrive in upcoming sprints.</p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
