<?php
$login = $login ?? ($form['login'] ?? ($form['student_id'] ?? ($form['email'] ?? '')));
$error = $error ?? null;
$success = $success ?? null;
$errors = $errors ?? [];

$inputClass = 'ux-input';
$passwordInputClass = 'ux-input pl-4 pr-12';
$labelClass = 'ux-label';

ob_start();
?>
<div class="auth-page-bg flex items-center justify-center px-4 sm:px-6 py-8 sm:py-12 md:py-16 min-h-[calc(100dvh-8rem)]">
    <div class="w-full max-w-md">
        <div class="auth-card ux-card p-6 sm:p-8">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20 mb-6 mx-auto">
                <i class="fa-solid fa-dumbbell text-slate-950 text-lg"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center tracking-tight"><?= escape(__('auth.login_title')) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mb-6 text-center text-sm"><?= escape(__('auth.login_subtitle')) ?></p>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php elseif ($success === 'password_reset'): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('auth.reset_password_success')) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= escape(url('/login')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div>
                    <label for="login" class="<?= escape($labelClass) ?>"><?= escape(__('auth.login_username')) ?></label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="<?= escape($login) ?>"
                        required
                        autocomplete="username"
                        class="<?= escape($inputClass) ?>"
                        placeholder="<?= escape(__('auth.login_username_placeholder')) ?>"
                    >
                    <?php if (!empty($errors['login'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['login'][0]) ?></p>
                    <?php elseif (!empty($errors['student_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['student_id'][0]) ?></p>
                    <?php elseif (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="<?= escape($labelClass) ?>"><?= escape(__('auth.password')) ?></label>
                    <div class="password-toggle-wrap relative">
                        <input type="password" id="password" name="password" required autocomplete="current-password" class="<?= escape($passwordInputClass) ?>">
                        <button
                            type="button"
                            class="password-toggle-btn absolute inset-y-0 right-0 flex items-center justify-center w-11 text-slate-400 hover:text-brand-500 dark:hover:text-brand-accent transition z-10"
                            aria-label="<?= escape(__('auth.show_password')) ?>"
                            aria-pressed="false"
                            data-label-show="<?= escape(__('auth.show_password')) ?>"
                            data-label-hide="<?= escape(__('auth.hide_password')) ?>"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['password'][0]) ?></p>
                    <?php endif; ?>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-brand-500 focus:ring-brand-500/30"
                                <?= !empty($form['remember']) ? 'checked' : '' ?>
                            >
                            <?= escape(__('auth.remember_me')) ?>
                        </label>
                        <a href="<?= escape(url('/forgot-password')) ?>" class="text-xs text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium whitespace-nowrap">
                            <?= escape(__('auth.forgot_password')) ?>
                        </a>
                    </div>
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
