<?php
$form = $form ?? [];
$error = $error ?? null;
$errors = $errors ?? [];

$inputClass = 'ux-input';
$passwordInputClass = 'ux-input pr-12';
$labelClass = 'ux-label';

ob_start();
?>
<div class="auth-page-bg flex items-center justify-center px-4 sm:px-6 py-8 sm:py-12 md:py-16 min-h-[calc(100dvh-8rem)]">
    <div class="w-full max-w-lg">
        <div class="auth-card ux-card p-6 sm:p-8">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20 mb-6 mx-auto">
                <i class="fa-solid fa-dumbbell text-slate-950 text-lg"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 text-center tracking-tight"><?= escape(__('auth.register_title')) ?></h1>
            <p class="text-slate-500 dark:text-slate-400 mb-6 text-center text-sm"><?= escape(__('auth.register_subtitle')) ?></p>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?= escape(url('/register')) ?>"
                class="space-y-4"
                data-progress
                data-progress-label="<?= escape(__('progress.creating_account')) ?>"
                data-progress-processing="<?= escape(__('progress.processing')) ?>"
            >
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div>
                    <label for="student_id" class="<?= escape($labelClass) ?>"><?= escape(__('auth.student_id')) ?></label>
                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        value="<?= escape($form['student_id'] ?? '') ?>"
                        required
                        autocomplete="username"
                        class="<?= escape($inputClass) ?>"
                        placeholder="<?= escape(__('auth.student_id_placeholder')) ?>"
                    >
                    <?php if (!empty($errors['student_id'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['student_id'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="title_prefix" class="<?= escape($labelClass) ?>"><?= escape(__('auth.title_prefix')) ?></label>
                    <input
                        type="text"
                        id="title_prefix"
                        name="title_prefix"
                        value="<?= escape($form['title_prefix'] ?? '') ?>"
                        required
                        class="<?= escape($inputClass) ?>"
                        placeholder="<?= escape(__('auth.title_prefix_placeholder')) ?>"
                    >
                    <?php if (!empty($errors['title_prefix'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['title_prefix'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.first_name')) ?></label>
                        <input type="text" id="first_name" name="first_name" value="<?= escape($form['first_name'] ?? '') ?>" required class="<?= escape($inputClass) ?>">
                        <?php if (!empty($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['first_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="last_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.last_name')) ?></label>
                        <input type="text" id="last_name" name="last_name" value="<?= escape($form['last_name'] ?? '') ?>" required class="<?= escape($inputClass) ?>">
                        <?php if (!empty($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['last_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="email" class="<?= escape($labelClass) ?>"><?= escape(__('auth.email')) ?></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escape($form['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                        class="<?= escape($inputClass) ?>"
                        placeholder="<?= escape(__('auth.email_placeholder')) ?>"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="<?= escape($labelClass) ?>"><?= escape(__('auth.password')) ?></label>
                    <div class="password-toggle-wrap relative">
                        <input type="password" id="password" name="password" required autocomplete="new-password" class="<?= escape($passwordInputClass) ?>">
                        <button
                            type="button"
                            class="password-toggle-btn absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-brand-500 dark:hover:text-brand-accent transition"
                            aria-label="<?= escape(__('auth.show_password')) ?>"
                            aria-pressed="false"
                            data-label-show="<?= escape(__('auth.show_password')) ?>"
                            data-label-hide="<?= escape(__('auth.hide_password')) ?>"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <?php foreach ($errors['password'] as $passwordError): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($passwordError) ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-slate-400"><?= escape(__('auth.password_hint')) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password_confirmation" class="<?= escape($labelClass) ?>"><?= escape(__('auth.password_confirmation')) ?></label>
                    <div class="password-toggle-wrap relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="<?= escape($passwordInputClass) ?>">
                        <button
                            type="button"
                            class="password-toggle-btn absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-brand-500 dark:hover:text-brand-accent transition"
                            aria-label="<?= escape(__('auth.show_password')) ?>"
                            aria-pressed="false"
                            data-label-show="<?= escape(__('auth.show_password')) ?>"
                            data-label-hide="<?= escape(__('auth.hide_password')) ?>"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password_confirmation'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['password_confirmation'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                    <?= escape(__('auth.create_account')) ?>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                <?= escape(__('auth.has_account')) ?>
                <a href="<?= escape(url('/login')) ?>" class="text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium"><?= escape(__('auth.sign_in')) ?></a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = true;
$showSidebar = false;
require base_path('app/Views/layouts/app.php');
