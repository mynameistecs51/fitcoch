<?php
$email = $email ?? '';
$error = $error ?? null;
$errors = $errors ?? [];
$success = $success ?? null;
$resetUrl = $reset_url ?? null;

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';

ob_start();
?>
<div class="flex items-center justify-center px-4 sm:px-6 py-8 sm:py-12 md:py-16 min-h-[calc(100dvh-8rem)]">
    <div class="w-full max-w-md">
        <div class="auth-card bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20 mb-6 mx-auto">
                <i class="fa-solid fa-key text-slate-950 text-lg"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center"><?= escape(__('auth.forgot_password_title')) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mb-6 text-center text-sm"><?= escape(__('auth.forgot_password_subtitle')) ?></p>

            <?php if ($success): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('auth.forgot_password_sent')) ?>
                    <?php if ($resetUrl): ?>
                        <p class="mt-3 text-xs break-all">
                            <span class="font-semibold"><?= escape(__('auth.forgot_password_dev_hint')) ?></span>
                            <a href="<?= escape($resetUrl) ?>" class="underline"><?= escape($resetUrl) ?></a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form
                    method="POST"
                    action="<?= escape(url('/forgot-password')) ?>"
                    class="space-y-4"
                    data-progress
                    data-progress-label="<?= escape(__('auth.forgot_password_submit')) ?>"
                    data-progress-processing="<?= escape(__('progress.processing')) ?>"
                >
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                    <div>
                        <label for="email" class="<?= escape($labelClass) ?>"><?= escape(__('auth.email')) ?></label>
                        <input type="email" id="email" name="email" value="<?= escape($email) ?>" required class="<?= escape($inputClass) ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['email'][0]) ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="w-full py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                        <?= escape(__('auth.forgot_password_submit')) ?>
                    </button>
                </form>
            <?php endif; ?>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                <a href="<?= escape(url('/login')) ?>" class="text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium"><?= escape(__('auth.back_to_sign_in')) ?></a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = true;
$showSidebar = false;
require base_path('app/Views/layouts/app.php');
