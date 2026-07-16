<?php
$course = $course ?? ['id' => 0, 'title' => '', 'description' => '', 'module_count' => 0];
$description = trim((string) $course['description']);
$excerpt = $description !== ''
    ? (mb_strlen($description) > 100 ? mb_substr($description, 0, 100) . '…' : $description)
    : __('home.no_description');
?>
<article
    class="landing-course-card group flex flex-col rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden hover:shadow-xl hover:shadow-sky-500/10 hover:border-sky-500/40 transition"
    data-course-title="<?= escape(mb_strtolower((string) $course['title'])) ?>"
    data-course-description="<?= escape(mb_strtolower((string) $course['description'])) ?>"
>
    <div class="landing-course-thumb h-36 bg-gradient-to-br from-sky-600 via-sky-500 to-brand-500 relative overflow-hidden">
        <div class="absolute inset-0 landing-course-thumb-pattern opacity-40"></div>
        <div class="absolute bottom-3 left-3 right-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-black/30 backdrop-blur text-[11px] font-semibold text-white">
                <i class="fa-solid fa-layer-group"></i>
                <?= escape(__('home.module_count', ['count' => (string) $course['module_count']])) ?>
            </span>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="fa-solid fa-dumbbell text-white"></i>
        </div>
    </div>
    <div class="flex flex-col flex-1 p-4">
        <h3 class="font-bold text-slate-900 dark:text-white leading-snug mb-2 line-clamp-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">
            <?= escape((string) $course['title']) ?>
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2 flex-1 mb-4">
            <?= escape($excerpt) ?>
        </p>
        <a
            href="<?= escape(url('/login')) ?>"
            class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-sky-500/10 hover:bg-sky-500 text-sky-700 hover:text-white dark:text-sky-400 dark:hover:text-white font-semibold text-sm transition"
        >
            <?= escape(__('home.enroll_cta')) ?>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
</article>
