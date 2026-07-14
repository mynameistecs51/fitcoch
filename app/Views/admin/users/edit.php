<?php
$user = $account['user'];
$roles = $account['roles'];
$errors = $errors ?? [];
$error = $error ?? null;

$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$labelTdClass = 'px-4 py-3 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap w-40';

ob_start();
?>
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
                <i class="fa-solid fa-user-pen text-brand-500 mr-3"></i>
                <?= escape(__('admin.edit_title')) ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape($user->email) ?></p>
        </div>
        <a href="<?= escape(url('/admin/users')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('admin.back_to_list')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-8">
        <?php if ($error === 'csrf'): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('errors.invalid_csrf')) ?>
            </div>
        <?php elseif (is_string($error) && $error !== ''): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3"><?= escape(__('admin.edit_title')) ?></h2>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.field')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.value')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('admin.table.name')) ?></td>
                            <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                <?= escape($user->firstName . ' ' . $user->lastName) ?>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('admin.table.email')) ?></td>
                            <td class="<?= escape($tdClass) ?>"><?= escape($user->email) ?></td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('admin.table.status')) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $user->status === 'active' ? 'bg-brand-500/10 text-brand-700 dark:text-brand-accent' : 'bg-red-500/10 text-red-600 dark:text-red-400' ?>">
                                    <?= escape(__('admin.status.' . $user->status)) ?>
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('admin.table.roles')) ?></td>
                            <td class="<?= escape($tdClass) ?>"><?= escape(translate_roles($roles)) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/roles')) ?>">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3"><?= escape(__('admin.assign_roles')) ?></h2>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
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

            <button type="submit" class="mt-4 w-full sm:w-auto px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                <?= escape(__('admin.save_roles')) ?>
            </button>
        </form>

        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3"><?= escape(__('admin.account_status')) ?></h2>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.field')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('admin.account_status')) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <?php if ($user->status === 'active'): ?>
                                    <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="px-4 py-2 text-sm border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                            <?= escape(__('admin.suspend_account')) ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="px-4 py-2 text-sm border border-brand-500/30 text-brand-700 dark:text-brand-accent rounded-xl hover:bg-brand-500/10 transition">
                                            <?= escape(__('admin.activate_account')) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'admin';
require base_path('app/Views/layouts/app.php');
