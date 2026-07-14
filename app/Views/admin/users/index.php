<?php
$success = $success ?? null;
$error = $error ?? null;
$importResult = $importResult ?? null;
$importError = $importError ?? null;

$inputClass = 'block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-500/10 file:text-brand-700 dark:file:text-brand-accent hover:file:bg-brand-500/20';
$cardClass = 'bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800';

ob_start();
?>
<section class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
            <i class="fa-solid fa-users-gear text-brand-500 mr-3"></i>
            <?= escape(__('admin.title')) ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('admin.subtitle')) ?></p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 <?= escape($cardClass) ?>">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center mb-2">
                <i class="fa-solid fa-file-arrow-up text-brand-500 mr-2"></i>
                <?= escape(__('admin.import.title')) ?>
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4"><?= escape(__('admin.import.subtitle')) ?></p>

            <div class="mb-5 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-2"><?= escape(__('admin.import.template_hint')) ?></p>
                <a
                    href="<?= escape(url('/admin/users/import/template')) ?>"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent transition"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    <?= escape(__('admin.import.download_template')) ?>
                </a>
            </div>

            <form
                method="POST"
                action="<?= escape(url('/admin/users/import')) ?>"
                enctype="multipart/form-data"
                class="space-y-4"
                data-progress
                data-progress-upload-label="<?= escape(__('progress.importing_users')) ?>"
                data-progress-processing="<?= escape(__('progress.processing')) ?>"
            >
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div>
                    <label for="user_file" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        <?= escape(__('admin.import.choose_file')) ?>
                    </label>
                    <input
                        type="file"
                        id="user_file"
                        name="user_file"
                        accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv"
                        required
                        class="<?= escape($inputClass) ?>"
                    >
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('admin.import.file_hint')) ?></p>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition duration-200 shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <?= escape(__('admin.import.submit')) ?>
                </button>
            </form>
        </div>

        <div class="xl:col-span-2 <?= escape($cardClass) ?>">
            <?php if ($success === 'roles_updated'): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.roles_updated')) ?>
                </div>
            <?php elseif ($success === 'status_updated'): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.status_updated')) ?>
                </div>
            <?php elseif ($success === 'profile_updated'): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.profile_updated')) ?>
                </div>
            <?php endif; ?>

            <?php if ($error === 'not_found'): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape(__('admin.validation.user_not_found')) ?>
                </div>
            <?php endif; ?>

            <?php if (is_string($importError) && $importError !== ''): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($importError) ?>
                </div>
            <?php endif; ?>

            <?php if (is_array($importResult)): ?>
                <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm space-y-3">
                    <p>
                        <?= escape(__('admin.import.result_summary', [
                            'created' => (string) ($importResult['created'] ?? 0),
                            'skipped' => (string) ($importResult['skipped'] ?? 0),
                        ])) ?>
                    </p>

                    <?php if (!empty($importResult['errors']) && is_array($importResult['errors'])): ?>
                        <div>
                            <p class="font-semibold mb-2"><?= escape(__('admin.import.result_errors')) ?></p>
                            <ul class="space-y-1 text-xs sm:text-sm">
                                <?php foreach ($importResult['errors'] as $rowNumber => $message): ?>
                                    <li>
                                        <span class="font-medium"><?= escape((string) $rowNumber) ?>:</span>
                                        <?= escape((string) $message) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
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
                                    <a href="<?= escape(url('/admin/users/' . $accountUser->id)) ?>" class="inline-flex items-center gap-1 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                        <i class="fa-solid fa-pen-to-square"></i>
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
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'admin';
require base_path('app/Views/layouts/app.php');
