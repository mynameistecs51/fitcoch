<?php
$email = $email ?? ($form['email'] ?? '');
$error = $error ?? null;
$errors = $errors ?? [];

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';

ob_start();
?>
<div class="flex items-center justify-center px-6 py-12 md:py-16 min-h-[calc(100vh-8rem)]">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20 mb-6 mx-auto">
                <i class="fa-solid fa-dumbbell text-slate-950 text-lg"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center"><?= escape(__('auth.login_title')) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mb-6 text-center text-sm"><?= escape(__('auth.login_subtitle')) ?></p>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= escape(url('/login')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div>
                    <label for="email" class="<?= escape($labelClass) ?>"><?= escape(__('auth.email')) ?></label>
                    <input type="email" id="email" name="email" value="<?= escape($email) ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="<?= escape($labelClass) ?>"><?= escape(__('auth.password')) ?></label>
                    <input type="password" id="password" name="password" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['password'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                    <?= escape(__('auth.sign_in')) ?>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                <?= escape(__('auth.no_account')) ?>
                <a href="<?= escape(url('/register')) ?>" class="text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium"><?= escape(__('auth.create_one')) ?></a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = true;
$showSidebar = false;
require base_path('app/Views/layouts/app.php');
