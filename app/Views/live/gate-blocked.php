<?php
ob_start();
?>
<section class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 text-center">
        <div class="w-16 h-16 rounded-2xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-lock text-2xl"></i>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape(__('live.gate_title')) ?></h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-3"><?= escape(__('live.gate_message')) ?></p>

        <div class="mt-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-left text-sm">
            <p class="text-slate-500 dark:text-slate-400"><?= escape(__('live.instructor.session_title')) ?></p>
            <p class="font-semibold text-slate-900 dark:text-slate-200"><?= escape($session->title) ?></p>
            <?php if ($ticket !== null): ?>
                <p class="text-slate-500 dark:text-slate-400 mt-3"><?= escape(__('quizzes.instructor.ticket_status')) ?></p>
                <p class="font-semibold text-slate-900 dark:text-slate-200"><?= escape(__('quizzes.ticket_status.' . $ticket->status)) ?></p>
            <?php endif; ?>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= escape(url('/courses/' . $course->id)) ?>" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
                <?= escape(__('live.gate_back_syllabus')) ?>
            </a>
            <?php if ($ticket === null || $ticket->status === 'locked'): ?>
                <a href="<?= escape(url('/courses/' . $course->id)) ?>" class="px-6 py-3 border border-brand-500/30 text-brand-700 dark:text-brand-accent font-bold rounded-xl hover:bg-brand-500/10 transition">
                    <?= escape(__('live.gate_take_quiz')) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
