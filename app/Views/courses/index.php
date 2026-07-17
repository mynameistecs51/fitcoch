<?php

ob_start();
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$availableCourses = $availableCourses ?? [];
$success = $success ?? null;
$error = $error ?? null;
$isInstructor = $isInstructor ?? false;
$unreadCounts = $unreadCounts ?? [];
$courseOutlines = $courseOutlines ?? [];
?>
<section class="space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center">
            <i class="fa-solid fa-circle-play text-brand-500 mr-3"></i>
            <?= escape(__('courses.title')) ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.subtitle')) ?></p>
    </div>

    <?php if ($success === 'enrolled'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
            <?= escape(__('courses.enrollment.success')) ?>
        </div>
    <?php endif; ?>

    <?php if ($error === 'csrf' || $error === __('errors.invalid_csrf')): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape(__('errors.invalid_csrf')) ?>
        </div>
    <?php elseif (is_string($error) && $error !== ''): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape(__('courses.enrollment.catalog_title')) ?></h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.enrollment.catalog_subtitle')) ?></p>
        </div>

        <?php if ($availableCourses === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-6"><?= escape(__('courses.enrollment.catalog_empty')) ?></p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($availableCourses as $course): ?>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 bg-slate-50/60 dark:bg-slate-950/40 flex flex-col gap-3">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white"><?= escape($course->title) ?></h3>
                            <?php if ($course->description): ?>
                                <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 line-clamp-3"><?= escape($course->description) ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="<?= escape(url('/courses/' . $course->id . '/enroll')) ?>" class="mt-auto">
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                            <button type="submit" class="w-full px-4 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition text-sm">
                                <i class="fa-solid fa-user-plus mr-1"></i><?= escape(__('courses.enrollment.enroll_button')) ?>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape(__('courses.enrollment.my_courses_title')) ?></h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('courses.enrollment.my_courses_subtitle')) ?></p>
        </div>

        <?php require base_path('app/Views/courses/index-my-courses.php'); ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
