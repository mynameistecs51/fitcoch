<?php
$form = $form ?? [];
$errors = $errors ?? [];
$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';

ob_start();
?>
<section class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape(__('live.instructor.title')) ?></h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?= escape($course->title) ?> — <?= escape($module->title) ?>
            </p>
        </div>
        <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('live.instructor.back_to_course')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
        <?php if ($success === 'created'): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('live.instructor.created')) ?>
            </div>
        <?php elseif ($success === 'activated'): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('live.instructor.activated')) ?>
            </div>
        <?php elseif ($success === 'completed'): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('live.instructor.completed')) ?>
            </div>
        <?php endif; ?>

        <?php if ($error === 'csrf'): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape(__('errors.invalid_csrf')) ?>
            </div>
        <?php elseif (is_string($error) && $error !== ''): ?>
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
                <?= escape($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/live-sessions')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('live.instructor.schedule_new')) ?></h2>

            <div>
                <label for="title" class="<?= escape($labelClass) ?>"><?= escape(__('live.instructor.session_title')) ?></label>
                <input type="text" id="title" name="title" value="<?= escape($form['title'] ?? '') ?>" required class="<?= escape($inputClass) ?>">
                <?php if (!empty($errors['title'])): ?>
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['title'][0]) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="<?= escape($labelClass) ?>"><?= escape(__('live.instructor.start_time')) ?></label>
                    <input type="datetime-local" id="start_time" name="start_time" value="<?= escape($form['start_time'] ?? '') ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['start_time'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['start_time'][0]) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="end_time" class="<?= escape($labelClass) ?>"><?= escape(__('live.instructor.end_time')) ?></label>
                    <input type="datetime-local" id="end_time" name="end_time" value="<?= escape($form['end_time'] ?? '') ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['end_time'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['end_time'][0]) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
                <?= escape(__('live.instructor.create_session')) ?>
            </button>
        </form>

        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3"><?= escape(__('live.instructor.existing_sessions')) ?></h2>
            <?php if ($sessions === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('live.instructor.no_sessions')) ?></p>
            <?php else: ?>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('live.instructor.session_title')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('live.instructor.schedule')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('live.table.status')) ?></th>
                                <th class="<?= escape($thClass) ?> text-right"><?= escape(__('live.table.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200"><?= escape($session->title) ?></td>
                                    <td class="<?= escape($tdClass) ?> text-xs">
                                        <?= escape($session->startTime) ?><br>
                                        <span class="text-slate-400">→ <?= escape($session->endTime) ?></span>
                                    </td>
                                    <td class="<?= escape($tdClass) ?>">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent">
                                            <?= escape(__('live.status.' . $session->status)) ?>
                                        </span>
                                    </td>
                                    <td class="<?= escape($tdClass) ?> text-right space-x-2">
                                        <?php if (in_array($session->status, ['scheduled', 'active'], true)): ?>
                                            <a href="<?= escape(url('/live/' . $session->id)) ?>" class="text-xs text-brand-600 dark:text-brand-500 hover:underline font-semibold">
                                                <?= escape(__('live.instructor.enter_room')) ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($session->status === 'scheduled'): ?>
                                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/live-sessions/' . $session->id . '/activate')) ?>" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                <button type="submit" class="text-xs text-brand-700 dark:text-brand-accent hover:underline font-semibold">
                                                    <?= escape(__('live.instructor.activate')) ?>
                                                </button>
                                            </form>
                                        <?php elseif ($session->status === 'active'): ?>
                                            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/live-sessions/' . $session->id . '/complete')) ?>" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                <button type="submit" class="text-xs text-slate-600 dark:text-slate-300 hover:underline font-semibold">
                                                    <?= escape(__('live.instructor.complete')) ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
