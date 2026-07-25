<?php
$success = $success ?? null;
$error = $error ?? null;
$importResult = $importResult ?? null;
$importError = $importError ?? null;
$accounts = $accounts ?? [];

$inputClass = 'block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-500/10 file:text-brand-700 dark:file:text-brand-accent hover:file:bg-brand-500/20';

$totalUsers = count($accounts);
$activeCount = 0;
$suspendedCount = 0;
$withStudentId = 0;

foreach ($accounts as $entry) {
    $u = $entry['user'];
    if ($u->status === 'active') {
        $activeCount++;
    } else {
        $suspendedCount++;
    }
    if ($u->studentId !== null && $u->studentId !== '') {
        $withStudentId++;
    }
}

$roleBadgeClass = static function (string $role): string {
    return match ($role) {
        'admin' => 'bg-violet-500/10 text-violet-700 dark:text-violet-300 border-violet-500/20',
        'instructor' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/20',
        'learner' => 'bg-brand-500/10 text-brand-700 dark:text-brand-accent border-brand-500/20',
        default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    };
};

$userInitials = static function (string $firstName, string $lastName): string {
    $first = mb_substr(trim($firstName), 0, 1);
    $last = mb_substr(trim($lastName), 0, 1);

    if ($first === '' && $last === '') {
        return '?';
    }

    return mb_strtoupper($first . $last);
};

$manageBtnClass = 'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-500/40 hover:text-brand-600 dark:hover:text-brand-accent transition shrink-0';

ob_start();
?>
<section class="space-y-8">
    <?php
    $heroTitle = __('admin.title');
    $heroSubtitle = __('admin.subtitle');
    $heroBadge = __('roles.admin');
    $heroBadgeIcon = 'fa-users-gear';
    require base_path('app/Views/partials/instructor-page-hero.php');
    ?>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div class="ux-stat-card ux-card p-5 md:p-6 col-span-2 lg:col-span-1">
            <div class="ux-stat-icon bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-users"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('admin.stats.total')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) $totalUsers) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('admin.stats.active')) ?></p>
            <p class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $activeCount) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-red-500/10 text-red-600 dark:text-red-400">
                <i class="fa-solid fa-ban"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('admin.stats.suspended')) ?></p>
            <p class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $suspendedCount) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('admin.stats.with_student_id')) ?></p>
            <p class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mt-1"><?= escape((string) $withStudentId) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">
        <div class="xl:col-span-4 ux-card p-6 md:p-8 space-y-5">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up text-brand-500"></i>
                    <?= escape(__('admin.import.title')) ?>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed"><?= escape(__('admin.import.subtitle')) ?></p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-3 leading-relaxed"><?= escape(__('admin.import.template_hint')) ?></p>
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

        <div class="xl:col-span-8 ux-card p-6 md:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-address-book text-brand-500"></i>
                        <?= escape(__('admin.list_title')) ?>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('admin.list_subtitle')) ?></p>
                </div>
                <span class="inline-flex items-center gap-2 self-start sm:self-auto px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-list-ul text-[10px] text-brand-500"></i>
                    <?= escape((string) $totalUsers) ?> <?= escape(__('admin.stats.total')) ?>
                </span>
            </div>

            <?php if ($success === 'roles_updated'): ?>
                <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.roles_updated')) ?>
                </div>
            <?php elseif ($success === 'status_updated'): ?>
                <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.status_updated')) ?>
                </div>
            <?php elseif ($success === 'profile_updated'): ?>
                <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                    <?= escape(__('admin.profile_updated')) ?>
                </div>
            <?php endif; ?>

            <?php if ($error === 'not_found'): ?>
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape(__('admin.validation.user_not_found')) ?>
                </div>
            <?php endif; ?>

            <?php if (is_string($importError) && $importError !== ''): ?>
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                    <?= escape($importError) ?>
                </div>
            <?php endif; ?>

            <?php if (is_array($importResult)): ?>
                <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm space-y-3">
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

            <?php if ($accounts === []): ?>
                <div class="flex flex-col items-center justify-center text-center px-4 py-14 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent mb-4">
                        <i class="fa-solid fa-user-plus text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm"><?= escape(__('admin.empty_list')) ?></p>
                </div>
            <?php else: ?>
                <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/90 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800">
                                <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= escape(__('admin.table.name')) ?></th>
                                <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= escape(__('admin.table.student_id')) ?></th>
                                <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= escape(__('admin.table.roles')) ?></th>
                                <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><?= escape(__('admin.table.status')) ?></th>
                                <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right"><?= escape(__('admin.table.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <?php foreach ($accounts as $entry): ?>
                                <?php
                                $accountUser = $entry['user'];
                                $roles = $entry['roles'];
                                $initials = $userInitials($accountUser->firstName, $accountUser->lastName);
                                ?>
                                <tr class="group hover:bg-brand-500/[0.03] dark:hover:bg-brand-500/[0.06] transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3 min-w-[200px]">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500/15 to-brand-500/5 text-sm font-bold text-brand-700 dark:text-brand-accent border border-brand-500/15">
                                                <?= escape($initials) ?>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                                    <?= escape($accountUser->fullName()) ?>
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5" title="<?= escape($accountUser->email) ?>">
                                                    <i class="fa-regular fa-envelope mr-1 opacity-70"></i><?= escape($accountUser->email) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($accountUser->studentId !== null && $accountUser->studentId !== ''): ?>
                                            <span class="inline-flex px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-mono font-semibold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                                <?= escape($accountUser->studentId) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-[220px]">
                                            <?php foreach ($roles as $role): ?>
                                                <span class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full border <?= escape($roleBadgeClass($role)) ?>">
                                                    <?= escape(__('roles.' . $role) !== 'roles.' . $role ? __('roles.' . $role) : $role) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full <?= $accountUser->status === 'active' ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $accountUser->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                                            <?= escape(__('admin.status.' . $accountUser->status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="<?= escape(url('/admin/users/' . $accountUser->id)) ?>" class="<?= escape($manageBtnClass) ?>">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            <?= escape(__('admin.manage')) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-3">
                    <?php foreach ($accounts as $entry): ?>
                        <?php
                        $accountUser = $entry['user'];
                        $roles = $entry['roles'];
                        $initials = $userInitials($accountUser->firstName, $accountUser->lastName);
                        ?>
                        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500/15 to-brand-500/5 text-sm font-bold text-brand-700 dark:text-brand-accent border border-brand-500/15">
                                    <?= escape($initials) ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white leading-snug"><?= escape($accountUser->fullName()) ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 break-all"><?= escape($accountUser->email) ?></p>
                                    <?php if ($accountUser->studentId !== null && $accountUser->studentId !== ''): ?>
                                        <p class="text-xs font-mono text-slate-600 dark:text-slate-300 mt-2">
                                            <?= escape(__('admin.table.student_id')) ?>:
                                            <span class="font-semibold"><?= escape($accountUser->studentId) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($roles as $role): ?>
                                    <span class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full border <?= escape($roleBadgeClass($role)) ?>">
                                        <?= escape(__('roles.' . $role) !== 'roles.' . $role ? __('roles.' . $role) : $role) ?>
                                    </span>
                                <?php endforeach; ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full <?= $accountUser->status === 'active' ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' ?>">
                                    <?= escape(__('admin.status.' . $accountUser->status)) ?>
                                </span>
                            </div>
                            <a href="<?= escape(url('/admin/users/' . $accountUser->id)) ?>" class="<?= escape($manageBtnClass) ?> w-full">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                <?= escape(__('admin.manage')) ?>
                            </a>
                        </article>
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
