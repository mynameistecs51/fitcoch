<?php
$course = $course ?? null;
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;
$isEdit = $course !== null;

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$labelTdClass = 'px-4 py-3 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap w-40';

ob_start();
?>
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                <?= escape($isEdit ? __('courses.instructor.edit_title') : __('courses.instructor.create_title')) ?>
            </h1>
        </div>
        <a href="<?= escape(url('/instructor/courses')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('courses.instructor.back')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-8">
        <?php if ($error === 'csrf' || $error === __('errors.invalid_csrf')): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('errors.invalid_csrf')) ?>
            </div>
        <?php elseif (is_string($error) && $error !== ''): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('courses.instructor.success.' . $success)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= escape(url($isEdit ? '/instructor/courses/' . $course->id : '/instructor/courses')) ?>">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 mb-4">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.field')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('admin.table.value')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <tr>
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.title')) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <input type="text" name="title" value="<?= escape($form['title'] ?? $course?->title ?? '') ?>" required class="<?= escape($inputClass) ?>">
                                <?php if (!empty($errors['title'])): ?>
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['title'][0]) ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.description')) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <textarea name="description" rows="3" class="<?= escape($inputClass) ?>"><?= escape($form['description'] ?? $course?->description ?? '') ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.status')) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <?php $selectedStatus = $form['status'] ?? $course?->status ?? 'draft'; ?>
                                <select name="status" class="<?= escape($inputClass) ?>">
                                    <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>><?= escape(__('courses.status.draft')) ?></option>
                                    <option value="published" <?= $selectedStatus === 'published' ? 'selected' : '' ?>><?= escape(__('courses.status.published')) ?></option>
                                    <option value="archived" <?= $selectedStatus === 'archived' ? 'selected' : '' ?>><?= escape(__('courses.status.archived')) ?></option>
                                </select>
                                <?php if (!empty($errors['status'])): ?>
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['status'][0]) ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                <?= escape($isEdit ? __('courses.instructor.save') : __('courses.instructor.create')) ?>
            </button>
        </form>

        <?php if ($isEdit): ?>
            <div class="border-t border-slate-200 dark:border-slate-800 pt-8">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4"><?= escape(__('courses.modules_title')) ?></h2>

                <?php if ($modules !== []): ?>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 mb-4">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="<?= escape($thClass) ?> w-16"><?= escape(__('courses.table.order')) ?></th>
                                    <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.module')) ?></th>
                                    <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent"><?= escape((string) $module->sequenceOrder) ?></td>
                                        <td class="<?= escape($tdClass) ?>"><?= escape($module->title) ?></td>
                                        <td class="<?= escape($tdClass) ?> text-right">
                                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/delete')) ?>" class="inline" onsubmit="return confirm('<?= escape(__('courses.instructor.confirm_delete_module')) ?>');">
                                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline"><?= escape(__('courses.instructor.delete_module')) ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules')) ?>" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <input type="text" name="title" placeholder="<?= escape(__('courses.form.module_title')) ?>" required class="<?= escape($inputClass) ?> flex-1">
                    <button type="submit" class="px-4 py-3 bg-slate-800 dark:bg-slate-700 text-white font-bold rounded-xl hover:bg-slate-700 dark:hover:bg-slate-600 text-sm whitespace-nowrap">
                        <?= escape(__('courses.instructor.add_module')) ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
