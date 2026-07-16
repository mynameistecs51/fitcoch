<?php

$panel = $panel ?? ['due_count' => 0, 'remaining_count' => 0, 'current' => null];
$current = $panel['current'] ?? null;
$dueCount = (int) ($panel['due_count'] ?? 0);

ob_start();
?>
<section class="space-y-6 max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-brain text-brand-500"></i>
                <?= escape(__('reviews.daily_title')) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('reviews.daily_subtitle')) ?></p>
        </div>
        <a href="<?= escape(url('/review/dashboard')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('reviews.back_review_dashboard')) ?>
        </a>
    </div>

    <?php if (!empty($success) && $success === 'rated'): ?>
        <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
            <?= escape(__('reviews.success.rated')) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="ux-alert-enter p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape($error === 'csrf' ? __('errors.invalid_csrf') : ($error === 'validation' ? __('errors.validation_failed') : (is_string($error) ? $error : __('errors.validation_failed')))) ?>
        </div>
    <?php endif; ?>

    <?php if ($dueCount === 0 || $current === null): ?>
        <div class="bg-white dark:bg-slate-900 p-10 rounded-3xl border border-slate-200 dark:border-slate-800 text-center space-y-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-check-double text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white"><?= escape(__('reviews.empty_title')) ?></h2>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('reviews.empty_hint')) ?></p>
            <a href="<?= escape(url('/courses')) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm">
                <?= escape(__('reviews.go_courses')) ?>
            </a>
        </div>
    <?php else: ?>
        <?php
            $item = $current['item'];
            $schedule = $current['schedule'];
        ?>
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span><?= escape(__('reviews.queue_progress', ['current' => '1', 'total' => (string) $dueCount])) ?></span>
            <span><?= escape($current['course_title']) ?></span>
        </div>

        <div id="review-card-wrap" class="review-card-wrap">
            <div id="review-card" class="flashcard-scene bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 md:p-10 shadow-sm">
                <div id="flashcard-inner" class="flashcard-inner">
                    <div id="card-front" class="flashcard-face flashcard-front space-y-4">
                        <p class="text-[11px] uppercase tracking-wide text-brand-700 dark:text-brand-accent font-semibold">
                            <?= escape(__('reviews.prompt_label')) ?>
                        </p>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white leading-snug">
                            <?= escape($item->conceptName) ?>
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300"><?= escape(__('reviews.recall_hint')) ?></p>
                        <button
                            type="button"
                            id="reveal-btn"
                            class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-500 text-slate-950 font-bold text-sm hover:bg-brand-accent shadow-lg shadow-brand-500/20"
                            aria-expanded="false"
                        >
                            <i class="fa-solid fa-eye"></i>
                            <?= escape(__('reviews.reveal_answer')) ?>
                        </button>
                    </div>

                    <div id="card-back" class="flashcard-face flashcard-back space-y-4">
                        <p class="text-[11px] uppercase tracking-wide text-slate-700 dark:text-slate-300 font-semibold">
                            <?= escape(__('reviews.answer_label')) ?>
                        </p>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-950 p-5 text-sm text-slate-800 dark:text-slate-200 leading-relaxed">
                            <?= escape($item->description ?? __('reviews.no_description')) ?>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            <?= escape(__('reviews.rating_hint')) ?>
                        </p>

                        <form
                            id="review-rating-form"
                            method="POST"
                            action="<?= escape(url('/review/daily/' . $item->id . '/respond')) ?>"
                            data-api-url="<?= escape(url('/api/v1/reviews/' . $item->id . '/respond')) ?>"
                            data-redirect-url="<?= escape(url('/review/daily')) ?>"
                            class="space-y-4"
                        >
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                <?php foreach ([0, 1, 2, 3, 4, 5] as $rating): ?>
                                    <?php
                                        $ratingBtnClass = $rating < 3
                                            ? 'border-red-300 dark:border-red-700 bg-red-100 dark:bg-red-950/50 text-slate-950 dark:text-red-100 hover:bg-red-200 dark:hover:bg-red-950/70'
                                            : 'border-brand-400 dark:border-brand-600 bg-brand-100 dark:bg-brand-950/50 text-slate-950 dark:text-brand-accent hover:bg-brand-200 dark:hover:bg-brand-950/70';
                                    ?>
                                    <button
                                        type="submit"
                                        name="rating"
                                        value="<?= escape((string) $rating) ?>"
                                        class="review-rating-btn p-3 rounded-xl border text-sm font-bold flex flex-col items-center <?= escape($ratingBtnClass) ?>"
                                    >
                                        <span class="text-base"><?= escape((string) $rating) ?></span>
                                        <span class="text-[10px] font-semibold mt-1 opacity-90"><?= escape(__('reviews.ratings.' . $rating)) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-xs text-center text-slate-600 dark:text-slate-400">
            <?= escape(__('reviews.schedule_meta', [
                'interval' => (string) $schedule->intervalDays,
                'next' => $schedule->nextReviewDate,
            ])) ?>
        </p>
    <?php endif; ?>
</section>

<script src="<?= escape(url('/assets/review-daily.js')) ?>"></script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'reviews';
require base_path('app/Views/layouts/app.php');
