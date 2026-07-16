<?php

ob_start();
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$enrollmentCounts = $enrollmentCounts ?? [];
$unreadCounts = $unreadCounts ?? [];
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
                <i class="fa-solid fa-chalkboard-user text-brand-500 mr-3"></i>
                <?= escape(__('courses.instructor.title')) ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.instructor.subtitle')) ?></p>
        </div>
        <a href="<?= escape(url('/instructor/courses/create')) ?>" class="px-4 py-2 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
            <?= escape(__('courses.instructor.create')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <?php if (!empty($success)): ?>
            <div class="mb-4 p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('courses.instructor.success.' . $success)) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950">
                    <tr>
                        <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.title')) ?></th>
                        <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.status')) ?></th>
                        <th class="<?= escape($thClass) ?>"><?= escape(__('courses.instructor.progress_stats.enrolled')) ?></th>
                        <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                        <th class="<?= escape($thClass) ?> text-center"><?= escape(__('discussion.unread_column')) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200"><?= escape($course->title) ?></td>
                            <td class="<?= escape($tdClass) ?>">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    <?= escape(__('courses.status.' . $course->status)) ?>
                                </span>
                            </td>
                            <td class="<?= escape($tdClass) ?>">
                                <span class="inline-flex items-center gap-1.5 font-semibold text-brand-600 dark:text-brand-accent">
                                    <i class="fa-solid fa-users text-xs"></i>
                                    <?= escape((string) ($enrollmentCounts[$course->id] ?? 0)) ?>
                                </span>
                            </td>
                            <td class="<?= escape($tdClass) ?> text-right whitespace-nowrap">
                                <div class="inline-flex flex-wrap items-center justify-end gap-3">
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/progress')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                        <?= escape(__('courses.instructor.view_progress')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/knowledge-items')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                        <?= escape(__('knowledge_items.instructor.manage')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/cohorts')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-medium">
                                        <?= escape(__('cohorts.instructor.manage')) ?>
                                    </a>
                                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-500 font-medium">
                                        <?= escape(__('courses.instructor.edit')) ?>
                                    </a>
                                </div>
                            </td>
                            <?php
                                $courseId = $course->id;
                                $unreadCount = (int) ($unreadCounts[$course->id] ?? 0);
                                $linkUrl = url('/instructor/courses/' . $course->id . '/edit');
                                require base_path('app/Views/partials/course-unread-messages.php');
                            ?>
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
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
