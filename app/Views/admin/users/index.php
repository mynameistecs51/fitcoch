<?php
$success = $success ?? null;
$error = $error ?? null;

ob_start();
?>
<div class="max-w-6xl mx-auto px-6 py-16">
    <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900"><?= escape(__('admin.title')) ?></h1>
                <p class="text-slate-500 mt-1"><?= escape(__('admin.subtitle')) ?></p>
            </div>
            <a href="<?= escape(url('/dashboard')) ?>" class="text-sm text-indigo-600 hover:text-indigo-700">
                <?= escape(__('nav.back')) ?>
            </a>
        </div>

        <?php if ($success === 'roles_updated'): ?>
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <?= escape(__('admin.roles_updated')) ?>
            </div>
        <?php elseif ($success === 'status_updated'): ?>
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <?= escape(__('admin.status_updated')) ?>
            </div>
        <?php endif; ?>

        <?php if ($error === 'not_found'): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <?= escape(__('admin.validation.user_not_found')) ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase"><?= escape(__('admin.table.name')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase"><?= escape(__('admin.table.email')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase"><?= escape(__('admin.table.roles')) ?></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase"><?= escape(__('admin.table.status')) ?></th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase"><?= escape(__('admin.table.actions')) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php foreach ($accounts as $entry): ?>
                        <?php $user = $entry['user']; ?>
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                <?= escape($user->firstName . ' ' . $user->lastName) ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600"><?= escape($user->email) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-600"><?= escape(translate_roles($entry['roles'])) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= escape(__('admin.status.' . $user->status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= escape(url('/admin/users/' . $user->id)) ?>" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    <?= escape(__('admin.manage')) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
