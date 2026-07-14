<?php
$success = $success ?? null;
$error = $error ?? null;

ob_start();
?>
<section class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
            <i class="fa-solid fa-users-gear text-brand-500 mr-3"></i>
            <?= escape(__('admin.title')) ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('admin.subtitle')) ?></p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <?php if ($success === 'roles_updated'): ?>
            <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('admin.roles_updated')) ?>
            </div>
        <?php elseif ($success === 'status_updated'): ?>
            <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('admin.status_updated')) ?>
            </div>
        <?php endif; ?>

        <?php if ($error === 'not_found'): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('admin.validation.user_not_found')) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase"><?= escape(__('admin.table.name')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase"><?= escape(__('admin.table.email')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase"><?= escape(__('admin.table.roles')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase"><?= escape(__('admin.table.status')) ?></th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase"><?= escape(__('admin.table.actions')) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    <?php foreach ($accounts as $entry): ?>
                        <?php $accountUser = $entry['user']; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-200">
                                <?= escape($accountUser->firstName . ' ' . $accountUser->lastName) ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400"><?= escape($accountUser->email) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400"><?= escape(translate_roles($entry['roles'])) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $accountUser->status === 'active' ? 'bg-brand-500/10 text-brand-700 dark:text-brand-accent' : 'bg-red-500/10 text-red-600 dark:text-red-400' ?>">
                                    <?= escape(__('admin.status.' . $accountUser->status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= escape(url('/admin/users/' . $accountUser->id)) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                    <?= escape(__('admin.manage')) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'admin';
require base_path('app/Views/layouts/app.php');
