<?php

$result = $result ?? null;

$ticket = $ticket ?? null;

$lessonNav = $lessonNav ?? null;



ob_start();

?>

<section class="lesson-page max-w-[1400px] mx-auto space-y-5">

    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">

        <div class="min-w-0">

            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-500 mb-2">

                <?= escape($course->title) ?> · <?= escape($module->title) ?>

            </p>

            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">

                <?= escape($quiz->title) ?>

            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">

                <?= escape(__('quizzes.passing_score', ['score' => (string) $quiz->passingScorePct])) ?>

            </p>

        </div>

        <a href="<?= escape(url('/courses/' . $course->id . '?view=syllabus')) ?>" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent shrink-0">
            <i class="fa-solid fa-arrow-left"></i>
            <?= escape(__('lesson.view_syllabus')) ?>
        </a>

    </div>



    <div class="lesson-grid">

        <div class="lesson-main min-w-0">

            <?php

            $questions = $questions;

            $error = $error ?? null;

            $compact = false;
            $retakeLessonUrl = $retakeLessonUrl ?? null;
            require base_path('app/Views/partials/lesson-quiz-panel.php');

            ?>

        </div>



        <?php if ($lessonNav !== null): ?>

            <?php require base_path('app/Views/partials/lesson-sidebar.php'); ?>

        <?php endif; ?>

    </div>

</section>

<?php

$content = ob_get_clean();

$showAuthLinks = false;

$showSidebar = true;

$currentNav = 'courses';

require base_path('app/Views/layouts/app.php');

