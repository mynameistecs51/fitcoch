<?php
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;

ob_start();
?>
<div class="flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-lg">
        <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900"><?= escape(__('profile.title')) ?></h1>
                    <p class="text-slate-500 mt-1"><?= escape(__('profile.subtitle')) ?></p>
                </div>
                <a href="<?= escape(url('/dashboard')) ?>" class="text-sm text-indigo-600 hover:text-indigo-700"><?= escape(__('nav.back')) ?></a>
            </div>

            <?php if ($success): ?>
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    <?= escape(__('profile.updated')) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-sm text-slate-500"><?= escape(__('auth.email')) ?></p>
                <p class="font-semibold"><?= escape($user->email) ?></p>
                <p class="text-sm text-slate-500 mt-3"><?= escape(__('profile.roles')) ?></p>
                <p class="font-semibold"><?= escape(translate_roles($roles)) ?></p>
            </div>

            <form method="POST" action="<?= escape(url('/profile')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1"><?= escape(__('auth.first_name')) ?></label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?= escape($form['first_name'] ?? $user->firstName) ?>"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                        <?php if (!empty($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= escape($errors['first_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1"><?= escape(__('auth.last_name')) ?></label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?= escape($form['last_name'] ?? $user->lastName) ?>"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                        <?php if (!empty($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= escape($errors['last_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-slate-700 mb-1"><?= escape(__('auth.timezone')) ?></label>
                    <select
                        id="timezone"
                        name="timezone"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                        <?php
                        $selectedTimezone = $form['timezone'] ?? $user->timezone;
                        foreach (['UTC', 'America/New_York', 'America/Los_Angeles', 'Europe/London', 'Asia/Bangkok'] as $tz):
                        ?>
                            <option value="<?= escape($tz) ?>" <?= $selectedTimezone === $tz ? 'selected' : '' ?>>
                                <?= escape($tz) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['timezone'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= escape($errors['timezone'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors"
                >
                    <?= escape(__('profile.save')) ?>
                </button>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
