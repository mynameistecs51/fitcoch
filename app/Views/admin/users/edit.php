<?php
$user = $account['user'];
$roles = $account['roles'];
$errors = $errors ?? [];
$error = $error ?? null;

ob_start();
?>
<div class="max-w-3xl mx-auto px-6 py-16">
    <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?= escape(__('admin.edit_title')) ?></h1>
                <p class="text-slate-500 mt-1"><?= escape($user->email) ?></p>
            </div>
            <a href="<?= escape(url('/admin/users')) ?>" class="text-sm text-indigo-600 hover:text-indigo-700">
                <?= escape(__('admin.back_to_list')) ?>
            </a>
        </div>

        <?php if ($error === 'csrf'): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <?= escape(__('errors.invalid_csrf')) ?>
            </div>
        <?php elseif (is_string($error) && $error !== ''): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <p class="text-sm text-slate-500"><?= escape(__('admin.table.name')) ?></p>
            <p class="font-semibold"><?= escape($user->firstName . ' ' . $user->lastName) ?></p>
            <p class="text-sm text-slate-500 mt-3"><?= escape(__('admin.table.status')) ?></p>
            <p class="font-semibold"><?= escape(__('admin.status.' . $user->status)) ?></p>
        </div>

        <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/roles')) ?>" class="space-y-4 mb-8">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div>
                <p class="text-sm font-medium text-slate-700 mb-3"><?= escape(__('admin.assign_roles')) ?></p>
                <div class="space-y-2">
                    <?php foreach ($availableRoles as $role): ?>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="<?= escape($role->name) ?>"
                                <?= in_array($role->name, $roles, true) ? 'checked' : '' ?>
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="font-medium text-slate-800"><?= escape(__('roles.' . $role->name)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($errors['roles'])): ?>
                    <?php foreach ($errors['roles'] as $message): ?>
                        <p class="mt-2 text-sm text-red-600"><?= escape($message) ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                <?= escape(__('admin.save_roles')) ?>
            </button>
        </form>

        <div class="border-t border-slate-200 pt-6">
            <p class="text-sm font-medium text-slate-700 mb-3"><?= escape(__('admin.account_status')) ?></p>
            <div class="flex gap-3">
                <?php if ($user->status === 'active'): ?>
                    <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <input type="hidden" name="status" value="suspended">
                        <button type="submit" class="px-4 py-2 text-sm border border-red-200 text-red-700 rounded-lg hover:bg-red-50 transition-colors">
                            <?= escape(__('admin.suspend_account')) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= escape(url('/admin/users/' . $user->id . '/status')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="px-4 py-2 text-sm border border-emerald-200 text-emerald-700 rounded-lg hover:bg-emerald-50 transition-colors">
                            <?= escape(__('admin.activate_account')) ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php if (!empty($errors['status'])): ?>
                <?php foreach ($errors['status'] as $message): ?>
                    <p class="mt-2 text-sm text-red-600"><?= escape($message) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
