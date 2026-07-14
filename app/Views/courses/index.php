<?php

ob_start();
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
?>
<section class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
            <i class="fa-solid fa-circle-play text-brand-500 mr-3"></i>
            <?= escape(__('courses.title')) ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.subtitle')) ?></p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <?php if ($courses === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-8"><?= escape(__('courses.empty')) ?></p>
        <?php else: ?>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.title')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.status')) ?></th>
                            <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($courses as $course): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                    <?= escape($course->title) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?>">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent">
                                        <?= escape(__('courses.status.' . $course->status)) ?>
                                    </span>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-right">
                                    <a href="<?= escape(url('/courses/' . $course->id)) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                        <?= escape(__('courses.view_syllabus')) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
