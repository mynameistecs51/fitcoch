        <?php if ($courses === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-8"><?= escape(__('courses.empty')) ?></p>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($courses as $course): ?>
                    <?php $outline = $courseOutlines[$course->id] ?? null; ?>
                    <article class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="px-4 py-4 md:px-5 md:py-5 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?= escape($course->title) ?></h3>
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent">
                                        <?= escape(__('courses.status.' . $course->status)) ?>
                                    </span>
                                    <?php if ($outline !== null): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            <?= escape(__('lesson.overall_progress', ['percent' => (string) $outline['overall_progress']])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 shrink-0">
                                <a href="<?= escape(url('/courses/' . $course->id . '?view=syllabus')) ?>" class="inline-flex items-center gap-2 text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-semibold">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <?= escape(__('courses.view_syllabus')) ?>
                                </a>
                                <?php if ($isInstructor): ?>
                                    <?php
                                        $courseId = $course->id;
                                        $unreadCount = (int) ($unreadCounts[$course->id] ?? 0);
                                        if ($unreadCount > 0):
                                    ?>
                                        <a
                                            href="<?= escape(url('/courses/' . $course->id . '?view=syllabus')) ?>"
                                            class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-full bg-red-500 text-white text-xs font-bold shadow-sm shadow-red-500/30 hover:bg-red-600 transition"
                                            title="<?= escape(__('discussion.unread_badge', ['count' => (string) $unreadCount])) ?>"
                                        >
                                            <i class="fa-solid fa-comment-dots mr-1"></i>
                                            <?= escape((string) $unreadCount) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-4 md:p-5">
                            <?php if ($outline !== null): ?>
                                <?php
                                    $syllabus = $outline;
                                    $compact = true;
                                    require base_path('app/Views/partials/course-lesson-structure.php');
                                ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
