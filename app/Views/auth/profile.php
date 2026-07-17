<?php
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;
$timezoneOptions = timezone_options();
$currentTimezone = $form['timezone'] ?? ($user->timezone !== '' ? $user->timezone : default_timezone());

$inputClass = 'ux-input';
$readonlyClass = 'ux-input ux-input-readonly';
$labelClass = 'ux-label';
$initials = mb_strtoupper(mb_substr($user->firstName, 0, 1) . mb_substr($user->lastName, 0, 1));

ob_start();
?>
<section class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-accent text-slate-950 flex items-center justify-center text-xl font-extrabold shadow-lg shadow-brand-500/25">
            <?= escape($initials) ?>
        </div>
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                <?= escape($user->fullName()) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('profile.subtitle')) ?></p>
        </div>
    </div>

    <div class="ux-card p-6 md:p-8 space-y-6">
        <?php if ($success): ?>
            <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('profile.updated')) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="ux-alert-enter p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="<?= escape(url('/profile')) ?>"
            class="space-y-6"
            data-progress
            data-progress-label="<?= escape(__('progress.saving_profile')) ?>"
            data-progress-processing="<?= escape(__('progress.processing')) ?>"
        >
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div class="space-y-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-950/40 border border-slate-200/60 dark:border-slate-800/60">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-500/10">
                            <i class="fa-solid fa-id-card text-brand-600 dark:text-brand-accent text-xs"></i>
                        </span>
                        <?= escape(__('profile.personal_info')) ?>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('profile.personal_info_hint')) ?></p>
                </div>

                <div>
                    <label for="student_id" class="<?= escape($labelClass) ?>"><?= escape(__('auth.student_id')) ?></label>
                    <input type="text" id="student_id" value="<?= escape($user->studentId ?? '') ?>" readonly class="<?= escape($readonlyClass) ?>">
                </div>

                <div>
                    <label for="title_prefix" class="<?= escape($labelClass) ?>"><?= escape(__('auth.title_prefix')) ?></label>
                    <input type="text" id="title_prefix" name="title_prefix" value="<?= escape($form['title_prefix'] ?? $user->titlePrefix) ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['title_prefix'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['title_prefix'][0]) ?></p>
                    <?php endif; ?>
                </div>

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

                <div>
                    <label for="timezone" class="<?= escape($labelClass) ?>"><?= escape(__('auth.timezone')) ?></label>
                    <select id="timezone" name="timezone" class="<?= escape($inputClass) ?>">
                        <?php if (!isset($timezoneOptions[$currentTimezone])): ?>
                            <option value="<?= escape($currentTimezone) ?>" selected><?= escape($currentTimezone) ?></option>
                        <?php endif; ?>
                        <?php foreach ($timezoneOptions as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= $currentTimezone === $value ? 'selected' : '' ?>>
                                <?= escape($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('profile.timezone_hint')) ?></p>
                    <?php if (!empty($errors['timezone'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['timezone'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-950/40 border border-slate-200/60 dark:border-slate-800/60">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-brand-500/10">
                            <i class="fa-solid fa-shield-halved text-brand-600 dark:text-brand-accent text-xs"></i>
                        </span>
                        <?= escape(__('profile.account_info')) ?>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('profile.account_info_hint')) ?></p>
                </div>

                <?php if (!str_ends_with($user->email, '@student.fitcoch.local')): ?>
                <div>
                    <label for="email" class="<?= escape($labelClass) ?>"><?= escape(__('auth.email')) ?></label>
                    <input type="email" id="email" value="<?= escape($user->email) ?>" readonly class="<?= escape($readonlyClass) ?>">
                </div>
                <?php endif; ?>

                <div>
                    <label for="roles" class="<?= escape($labelClass) ?>"><?= escape(__('profile.roles')) ?></label>
                    <input type="text" id="roles" value="<?= escape(translate_roles($roles)) ?>" readonly class="<?= escape($readonlyClass) ?>">
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
