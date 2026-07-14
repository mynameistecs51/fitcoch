<?php
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';

ob_start();
?>
<section class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
            <i class="fa-solid fa-user-gear text-brand-500 mr-3"></i>
            <?= escape(__('profile.title')) ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('profile.subtitle')) ?></p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <?php if ($success): ?>
            <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('profile.updated')) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('auth.email')) ?></p>
            <p class="font-semibold text-slate-900 dark:text-slate-200"><?= escape($user->email) ?></p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-3"><?= escape(__('profile.roles')) ?></p>
            <p class="font-semibold text-brand-600 dark:text-brand-accent"><?= escape(translate_roles($roles)) ?></p>
        </div>

        <form method="POST" action="<?= escape(url('/profile')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.first_name')) ?></label>
                    <input type="text" id="first_name" name="first_name" value="<?= escape($form['first_name'] ?? $user->firstName) ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['first_name'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['first_name'][0]) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="last_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.last_name')) ?></label>
                    <input type="text" id="last_name" name="last_name" value="<?= escape($form['last_name'] ?? $user->lastName) ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['last_name'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['last_name'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                <?= escape(__('profile.save')) ?>
            </button>
        </form>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'profile';
require base_path('app/Views/layouts/app.php');
