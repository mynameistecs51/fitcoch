<?php

use App\Models\Course;

/** @var Course|null $course */
$course = $course ?? null;
$form = $form ?? [];
$errors = $errors ?? [];
$error = $error ?? null;
$success = $success ?? null;
$isEdit = $course instanceof Course;
$courseId = $course?->id ?? 0;
$moduleUpdateBase = $courseId > 0 ? url('/instructor/courses/' . $courseId . '/modules/') : '';
$nuggetsByModule = $nuggetsByModule ?? [];
$quizzesByModule = $quizzesByModule ?? [];
$moduleEditData = $moduleEditData ?? [];
$introVideoNugget = $introVideoNugget ?? null;
$introYoutubeId = $introYoutubeId ?? null;

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
$labelTdClass = 'px-4 py-3 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap w-40';

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                <?= escape($isEdit ? __('courses.instructor.edit_title') : __('courses.instructor.create_title')) ?>
            </h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <?php if ($isEdit): ?>
                <a href="<?= escape(url('/instructor/courses/' . $courseId . '/cohorts')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-semibold">
                    <?= escape(__('cohorts.instructor.manage')) ?>
                </a>
                <a href="<?= escape(url('/instructor/courses/' . $courseId . '/progress')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-semibold">
                    <?= escape(__('courses.instructor.view_progress')) ?>
                </a>
            <?php endif; ?>
            <a href="<?= escape(url('/instructor/courses')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                <?= escape(__('courses.instructor.back')) ?>
            </a>
        </div>
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

        <form
            method="POST"
            enctype="multipart/form-data"
            action="<?= escape(url($isEdit ? '/instructor/courses/' . $courseId : '/instructor/courses')) ?>"
            data-progress
            data-progress-mode="auto"
            data-progress-label="<?= escape(__('progress.saving_course')) ?>"
            data-progress-upload-label="<?= escape(__('progress.uploading_video')) ?>"
            data-progress-processing="<?= escape(__('progress.processing')) ?>"
        >
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800 mb-4">
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
                        <?php
                            $existingVideoNugget = ($fieldPrefix ?? '') === '' ? $introVideoNugget : null;
                            $existingYoutubeId = ($fieldPrefix ?? '') === '' ? $introYoutubeId : null;
                            require base_path('app/Views/partials/video-fields.php');
                        ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition shadow-lg shadow-brand-500/20">
                <?= escape($isEdit ? __('courses.instructor.save') : __('courses.instructor.create')) ?>
            </button>
        </form>

        <?php if ($isEdit): ?>
            <?php $modules = $modules ?? []; ?>
            <div class="border-t border-slate-200 dark:border-slate-800 pt-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('courses.modules_title')) ?></h2>
                    <button
                        type="button"
                        id="open-add-module-modal"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20"
                    >
                        <i class="fa-solid fa-plus"></i>
                        <?= escape(__('courses.instructor.add_module')) ?>
                    </button>
                </div>

                <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr>
                                <th class="<?= escape($thClass) ?> w-16"><?= escape(__('courses.table.order')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.module')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.content')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.quiz')) ?></th>
                                <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <?php if ($modules === []): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        <?= escape(__('courses.no_modules')) ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($modules as $module): ?>
                                    <?php
                                        $moduleNuggets = $nuggetsByModule[$module->id] ?? [];
                                        $moduleQuiz = ($quizzesByModule ?? [])[$module->id] ?? null;
                                        $videoNugget = null;
                                        foreach ($moduleNuggets as $nugget) {
                                            if ($nugget->nuggetType === 'video') {
                                                $videoNugget = $nugget;
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                        <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent whitespace-nowrap">
                                            <?= escape((string) $module->sequenceOrder) ?>
                                        </td>
                                        <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                            <?= escape($module->title) ?>
                                        </td>
                                        <td class="<?= escape($tdClass) ?>">
                                            <?php if ($videoNugget !== null): ?>
                                                <div class="flex items-start gap-2">
                                                    <i class="fa-solid fa-circle-play text-brand-500 mt-0.5 shrink-0"></i>
                                                    <div>
                                                        <p><?= escape($videoNugget->title) ?></p>
                                                        <?php if ($videoNugget->isYoutubeVideo()): ?>
                                                            <span class="text-xs text-brand-600 dark:text-brand-500"><?= escape(__('courses.nugget_youtube')) ?></span>
                                                        <?php elseif ($videoNugget->contentUrl): ?>
                                                            <span class="text-xs text-brand-600 dark:text-brand-500"><?= escape(__('courses.nugget_uploaded')) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400"><?= escape(__('courses.no_nuggets')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?= escape($tdClass) ?>">
                                            <?php if ($moduleQuiz): ?>
                                                <div>
                                                    <p class="font-medium"><?= escape($moduleQuiz->title) ?></p>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400"><?= escape((string) $moduleQuiz->passingScorePct) ?>%</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400"><?= escape(__('courses.table.no_quiz')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?= escape($tdClass) ?> text-right whitespace-nowrap">
                                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="edit-module-btn inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                                                    data-module-id="<?= escape((string) $module->id) ?>"
                                                >
                                                    <i class="fa-solid fa-pen"></i><?= escape(__('courses.instructor.edit_module')) ?>
                                                </button>
                                                <a href="<?= escape(url('/instructor/courses/' . $courseId . '/modules/' . $module->id . '/quiz')) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-500/10 text-brand-700 dark:text-brand-accent hover:bg-brand-500/20 transition">
                                                    <i class="fa-solid fa-clipboard-question"></i><?= escape(__('quizzes.instructor.manage_quiz')) ?>
                                                </a>
                                                <a href="<?= escape(url('/instructor/courses/' . $courseId . '/modules/' . $module->id . '/readiness')) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                    <?= escape(__('quizzes.instructor.manage_readiness')) ?>
                                                </a>
                                                <a href="<?= escape(url('/instructor/courses/' . $courseId . '/modules/' . $module->id . '/live-sessions')) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                    <?= escape(__('live.instructor.manage_sessions')) ?>
                                                </a>
                                                <form method="POST" action="<?= escape(url('/instructor/courses/' . $courseId . '/modules/' . $module->id . '/delete')) ?>" class="inline" onsubmit="return confirm('<?= escape(__('courses.instructor.confirm_delete_module')) ?>');">
                                                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                                        <i class="fa-solid fa-trash"></i><?= escape(__('courses.instructor.delete_module')) ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="edit-module-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="edit-module-modal-title">
                <div class="app-modal-backdrop" data-close-edit-modal></div>
                <div class="app-modal-panel">
                    <div class="app-modal-header">
                        <h3 id="edit-module-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">
                            <?= escape(__('courses.instructor.edit_module')) ?>
                        </h3>
                        <button type="button" class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" data-close-edit-modal aria-label="<?= escape(__('courses.modal.cancel')) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action=""
                        id="edit-module-form"
                        data-progress
                        data-progress-mode="auto"
                        data-progress-label="<?= escape(__('progress.updating_module')) ?>"
                        data-progress-upload-label="<?= escape(__('progress.uploading_video')) ?>"
                        data-progress-processing="<?= escape(__('progress.processing')) ?>"
                    >
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <div class="app-modal-body">
                            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        <tr>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.module_title')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <input type="text" name="title" id="edit-module-title" required class="<?= escape($inputClass) ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.video_section')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3"><?= escape(__('courses.form.video_hint')) ?></p>
                                                <div class="flex flex-wrap gap-4 text-sm">
                                                    <?php foreach (['none' => __('courses.form.video_none'), 'youtube' => __('courses.form.video_youtube'), 'upload' => __('courses.form.video_upload')] as $value => $label): ?>
                                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                                            <input
                                                                type="radio"
                                                                name="video_source"
                                                                value="<?= escape($value) ?>"
                                                                class="video-source-radio"
                                                                data-prefix=""
                                                            >
                                                            <span><?= escape($label) ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="video-field-row" data-prefix="" data-source="youtube" hidden>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.youtube_url')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <input type="url" name="youtube_url" id="edit-module-youtube-url" placeholder="https://www.youtube.com/watch?v=..." class="<?= escape($inputClass) ?>">
                                                <div id="edit-youtube-preview-wrap" class="mt-4 hidden rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-950 aspect-video max-w-xl">
                                                    <iframe id="edit-youtube-preview" class="w-full h-full" src="" title="<?= escape(__('courses.form.current_video')) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="video-field-row" data-prefix="" data-source="upload" hidden>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.video_file')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <div id="edit-uploaded-video-wrap" class="mb-3 hidden p-3 rounded-xl bg-brand-500/10 border border-brand-500/20 text-sm">
                                                    <p class="font-semibold text-brand-700 dark:text-brand-accent mb-1">
                                                        <i class="fa-solid fa-file-video mr-1"></i><?= escape(__('courses.form.current_video')) ?>
                                                    </p>
                                                    <p id="edit-uploaded-video-title" class="text-slate-700 dark:text-slate-300"></p>
                                                    <a id="edit-uploaded-video-link" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i><?= escape(__('courses.form.view_current_video')) ?>
                                                    </a>
                                                </div>
                                                <p id="edit-replace-video-hint" class="text-xs text-slate-500 dark:text-slate-400 mb-2 hidden"><?= escape(__('courses.form.replace_video_hint')) ?></p>
                                                <input type="file" name="video_file" accept="video/mp4,video/webm,.mp4,.webm" class="block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-500 file:text-slate-950 file:font-semibold hover:file:bg-brand-accent">
                                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('courses.form.video_file_hint')) ?></p>
                                            </td>
                                        </tr>
                                        <tr class="video-field-row" data-prefix="" data-source="youtube upload" hidden>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.nugget_title')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <input type="text" name="nugget_title" id="edit-module-nugget-title" placeholder="<?= escape(__('courses.form.nugget_title_placeholder')) ?>" class="<?= escape($inputClass) ?>">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="app-modal-footer">
                            <button type="button" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-close-edit-modal>
                                <?= escape(__('courses.modal.cancel')) ?>
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                                <?= escape(__('courses.instructor.save_module')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script type="application/json" id="module-edit-data"><?= json_encode($moduleEditData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>

            <div id="add-module-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="add-module-modal-title">
                <div class="app-modal-backdrop" data-close-modal></div>
                <div class="app-modal-panel">
                    <div class="app-modal-header">
                        <h3 id="add-module-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">
                            <?= escape(__('courses.instructor.add_module')) ?>
                        </h3>
                        <button type="button" class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" data-close-modal aria-label="<?= escape(__('courses.modal.cancel')) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action="<?= escape(url('/instructor/courses/' . $courseId . '/modules')) ?>"
                        id="add-module-form"
                        data-progress
                        data-progress-mode="auto"
                        data-progress-label="<?= escape(__('progress.adding_module')) ?>"
                        data-progress-upload-label="<?= escape(__('progress.uploading_video')) ?>"
                        data-progress-processing="<?= escape(__('progress.processing')) ?>"
                    >
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <div class="app-modal-body">
                            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        <tr>
                                            <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.module_title_new')) ?></td>
                                            <td class="<?= escape($tdClass) ?>">
                                                <input type="text" name="title" placeholder="<?= escape(__('courses.form.module_title_new')) ?>" required class="<?= escape($inputClass) ?>">
                                            </td>
                                        </tr>
                                        <?php
                                            $formBackup = $form;
                                            $form = [];
                                            $existingVideoNugget = null;
                                            $existingYoutubeId = null;
                                            $showModuleTitle = false;
                                            require base_path('app/Views/partials/video-fields.php');
                                            $form = $formBackup;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="app-modal-footer">
                            <button type="button" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-close-modal>
                                <?= escape(__('courses.modal.cancel')) ?>
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                                <?= escape(__('courses.instructor.add_module')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
document.querySelectorAll('form').forEach((form) => {
    form.querySelectorAll('.video-source-radio').forEach((radio) => {
        radio.addEventListener('change', () => {
            const prefix = radio.dataset.prefix || '';
            form.querySelectorAll(`.video-field-row[data-prefix="${prefix}"]`).forEach((row) => {
                const sources = (row.dataset.source || '').split(' ');
                row.hidden = !sources.includes(radio.value);
            });
        });
    });
});

(function () {
    const modal = document.getElementById('add-module-modal');
    const openBtn = document.getElementById('open-add-module-modal');
    const moduleForm = document.getElementById('add-module-form');

    if (!modal || !openBtn) {
        return;
    }

    const openModal = () => {
        modal.classList.remove('hidden');
        document.body.classList.add('app-modal-open');
        const titleInput = modal.querySelector('input[name="title"]');
        if (titleInput) {
            titleInput.focus();
        }
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('app-modal-open');
    };

    openBtn.addEventListener('click', openModal);

    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (moduleForm) {
        moduleForm.addEventListener('submit', closeModal);
    }
})();

(function () {
    const modal = document.getElementById('edit-module-modal');
    const editForm = document.getElementById('edit-module-form');
    const dataEl = document.getElementById('module-edit-data');
    const courseId = <?= json_encode($isEdit ? (string) $courseId : '') ?>;
    const moduleUpdateBase = <?= json_encode($moduleUpdateBase) ?>;

    if (!modal || !editForm || !dataEl || !courseId || !moduleUpdateBase) {
        return;
    }

    let moduleEditData = {};

    try {
        moduleEditData = JSON.parse(dataEl.textContent || '{}');
    } catch (error) {
        moduleEditData = {};
    }

    const titleInput = document.getElementById('edit-module-title');
    const nuggetTitleInput = document.getElementById('edit-module-nugget-title');
    const youtubeInput = document.getElementById('edit-module-youtube-url');
    const youtubePreviewWrap = document.getElementById('edit-youtube-preview-wrap');
    const youtubePreview = document.getElementById('edit-youtube-preview');
    const uploadedWrap = document.getElementById('edit-uploaded-video-wrap');
    const uploadedTitle = document.getElementById('edit-uploaded-video-title');
    const uploadedLink = document.getElementById('edit-uploaded-video-link');
    const replaceHint = document.getElementById('edit-replace-video-hint');

    const setVideoSource = (source) => {
        editForm.querySelectorAll('.video-source-radio').forEach((radio) => {
            radio.checked = radio.value === source;
        });
        editForm.querySelectorAll('.video-field-row[data-prefix=""]').forEach((row) => {
            const sources = (row.dataset.source || '').split(' ');
            row.hidden = !sources.includes(source);
        });
    };

    const openEditModal = (moduleId) => {
        const payload = moduleEditData[String(moduleId)] || moduleEditData[moduleId];

        if (!payload) {
            return;
        }

        editForm.action = `${moduleUpdateBase}${moduleId}`;
        if (titleInput) titleInput.value = payload.title || '';
        if (nuggetTitleInput) nuggetTitleInput.value = payload.nugget_title || '';
        if (youtubeInput) youtubeInput.value = payload.youtube_url || '';

        const source = payload.video_source || 'none';
        setVideoSource(source);

        if (youtubePreviewWrap && youtubePreview) {
            if (source === 'youtube' && payload.youtube_id) {
                youtubePreview.src = `https://www.youtube.com/embed/${payload.youtube_id}?rel=0`;
                youtubePreviewWrap.classList.remove('hidden');
            } else {
                youtubePreview.src = '';
                youtubePreviewWrap.classList.add('hidden');
            }
        }

        if (uploadedWrap && uploadedTitle && uploadedLink && replaceHint) {
            if (source === 'upload' && payload.has_uploaded_video) {
                uploadedTitle.textContent = payload.uploaded_video_title || '';
                uploadedLink.href = payload.uploaded_video_url || '#';
                uploadedWrap.classList.remove('hidden');
                replaceHint.classList.remove('hidden');
            } else {
                uploadedWrap.classList.add('hidden');
                replaceHint.classList.add('hidden');
            }
        }

        const fileInput = editForm.querySelector('input[name="video_file"]');
        if (fileInput) {
            fileInput.value = '';
        }

        modal.classList.remove('hidden');
        document.body.classList.add('app-modal-open');
        titleInput?.focus();
    };

    const closeEditModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('app-modal-open');
    };

    document.querySelectorAll('.edit-module-btn').forEach((button) => {
        button.addEventListener('click', () => {
            openEditModal(button.dataset.moduleId);
        });
    });

    modal.querySelectorAll('[data-close-edit-modal]').forEach((el) => {
        el.addEventListener('click', closeEditModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeEditModal();
        }
    });

    editForm.addEventListener('submit', closeEditModal);
})();
</script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
