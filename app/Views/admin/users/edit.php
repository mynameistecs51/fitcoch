<?php
$user = $account['user'];
$roles = $account['roles'];
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$cardClass = 'bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';

$initials = mb_strtoupper(mb_substr($user->firstName, 0, 1) . mb_substr($user->lastName, 0, 1));
$successMessages = [
    'profile_updated' => __('admin.profile_updated'),
    'roles_updated' => __('admin.roles_updated'),
    'status_updated' => __('admin.status_updated'),
];

ob_start();
?>
<section class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent flex items-center justify-center text-lg font-extrabold">
                <?= escape($initials) ?>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center flex-wrap gap-2">
                    <span><?= escape($user->firstName . ' ' . $user->lastName) ?></span>
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full <?= $user->status === 'active' ? 'bg-brand-500/10 text-brand-700 dark:text-brand-accent' : 'bg-red-500/10 text-red-600 dark:text-red-400' ?>">
                        <?= escape(__('admin.status.' . $user->status)) ?>
                    </span>
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape($user->email) ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <?= escape(__('admin.current_roles')) ?>: <span class="font-semibold text-brand-600 dark:text-brand-accent"><?= escape(translate_roles($roles)) ?></span>
                </p>
            </div>
        </div>
        <a href="<?= escape(url('/admin/users')) ?>" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent transition">
            <i class="fa-solid fa-arrow-left"></i>
            <?= escape(__('admin.back_to_list')) ?>
        </a>
    </div>

    <?php if (is_string($success) && isset($successMessages[$success])): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
            <?= escape($successMessages[$success]) ?>
        </div>
    <?php endif; ?>

    <?php if ($error === 'csrf'): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape(__('errors.invalid_csrf')) ?>
        </div>
    <?php elseif (is_string($error) && $error !== ''): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="<?= escape($cardClass) ?>">
            <div class="flex items-center justify-between gap-3 mb-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center">
                    <i class="fa-solid fa-id-card text-brand-500 mr-2"></i>
                    <?= escape(__('admin.account_info')) ?>
                </h2>
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                    <?= escape(__('admin.edit_account')) ?>
                </span>
            </div>

            <form
                method="POST"
                action="<?= escape(url('/admin/users/' . $user->id)) ?>"
                class="space-y-4"
                data-progress
                data-progress-label="<?= escape(__('progress.saving_profile')) ?>"
                data-progress-processing="<?= escape(__('progress.processing')) ?>"
            >
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.first_name')) ?></label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?= escape($form['first_name'] ?? $user->firstName) ?>"
                            required
                            class="<?= escape($inputClass) ?>"
                        >
                        <?php if (!empty($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['first_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="last_name" class="<?= escape($labelClass) ?>"><?= escape(__('auth.last_name')) ?></label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?= escape($form['last_name'] ?? $user->lastName) ?>"
                            required
                            class="<?= escape($inputClass) ?>"
                        >
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
                        value="<?= escape($form['email'] ?? $user->email) ?>"
                        required
                        class="<?= escape($inputClass) ?>"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= escape(__('admin.save_profile')) ?>
                </button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="<?= escape($cardClass) ?>">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center">
                    <i class="fa-solid fa-user-shield text-brand-500 mr-2"></i>
                    <?= escape(__('admin.assign_roles')) ?>
                </h2>

                <form
                    method="POST"
                    action="<?= escape(url('/admin/users/' . $user->id . '/roles')) ?>"
                    data-progress
                    data-progress-label="<?= escape(__('progress.saving_roles')) ?>"
                    data-progress-processing="<?= escape(__('progress.processing')) ?>"
                >
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.roles')) ?></th>
                                    <th class="<?= escape($thClass) ?> text-center w-28"><?= escape(__('admin.table.select')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                <?php foreach ($availableRoles as $role): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                        <td class="<?= escape($tdClass) ?> font-medium text-slate-900 dark:text-slate-200">
                                            <?= escape(__('roles.' . $role->name)) ?>
                                        </td>
                                        <td class="<?= escape($tdClass) ?> text-center">
                                            <input
                                                type="checkbox"
                                                name="roles[]"
                                                value="<?= escape($role->name) ?>"
                                                <?= in_array($role->name, $roles, true) ? 'checked' : '' ?>
                                                class="rounded border-slate-300 dark:border-slate-600 text-brand-500 focus:ring-brand-500 w-4 h-4"
                                            >
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($errors['roles'])): ?>
                        <div class="mt-3 space-y-1">
                            <?php foreach ($errors['roles'] as $message): ?>
                                <p class="text-sm text-red-600 dark:text-red-400"><?= escape($message) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="mt-4 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                        <i class="fa-solid fa-check"></i>
                        <?= escape(__('admin.save_roles')) ?>
                    </button>
                </form>
            </div>

            <div class="<?= escape($cardClass) ?>">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-brand-500 mr-2"></i>
                    <?= escape(__('admin.account_status')) ?>
                </h2>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('admin.table.status')) ?></p>
                        <p class="mt-1 font-semibold text-slate-900 dark:text-slate-200">
                            <?= escape(__('admin.status.' . $user->status)) ?>
                        </p>
                    </div>

                    <?php if ($user->status === 'active'): ?>
                        <form
                            method="POST"
                            action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>"
                            class="w-full sm:w-auto"
                            data-progress
                            data-progress-label="<?= escape(__('progress.updating_status')) ?>"
                            data-progress-processing="<?= escape(__('progress.processing')) ?>"
                        >
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                            <input type="hidden" name="status" value="suspended">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                <i class="fa-solid fa-ban"></i>
                                <?= escape(__('admin.suspend_account')) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form
                            method="POST"
                            action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>"
                            class="w-full sm:w-auto"
                            data-progress
                            data-progress-label="<?= escape(__('progress.updating_status')) ?>"
                            data-progress-processing="<?= escape(__('progress.processing')) ?>"
                        >
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold border border-brand-500/30 text-brand-700 dark:text-brand-accent rounded-xl hover:bg-brand-500/10 transition">
                                <i class="fa-solid fa-circle-check"></i>
                                <?= escape(__('admin.activate_account')) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!empty($errors['status'])): ?>
                    <div class="mt-3 space-y-1">
                        <?php foreach ($errors['status'] as $message): ?>
                            <p class="text-sm text-red-600 dark:text-red-400"><?= escape($message) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'admin';
require base_path('app/Views/layouts/app.php');
