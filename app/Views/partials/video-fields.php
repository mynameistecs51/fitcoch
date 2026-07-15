<?php
$fieldPrefix = $fieldPrefix ?? '';
$selectedSource = $form[$fieldPrefix . 'video_source'] ?? 'none';
$existingVideoNugget = $existingVideoNugget ?? null;
$existingYoutubeId = $existingYoutubeId ?? null;
$inputClass = $inputClass ?? 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelTdClass = $labelTdClass ?? 'px-4 py-3 text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap w-40';
$tdClass = $tdClass ?? 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
?>
<tr>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.video_section')) ?></td>
    <td class="<?= escape($tdClass) ?>">
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3"><?= escape(__('courses.form.video_hint')) ?></p>
        <div class="flex flex-wrap gap-4 text-sm">
            <?php foreach (['none' => __('courses.form.video_none'), 'youtube' => __('courses.form.video_youtube'), 'upload' => __('courses.form.video_upload')] as $value => $label): ?>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        name="<?= escape($fieldPrefix) ?>video_source"
                        value="<?= escape($value) ?>"
                        class="video-source-radio"
                        data-prefix="<?= escape($fieldPrefix) ?>"
                        <?= $selectedSource === $value ? 'checked' : '' ?>
                    >
                    <span><?= escape($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </td>
</tr>
<tr class="video-field-row" data-prefix="<?= escape($fieldPrefix) ?>" data-source="youtube" <?= $selectedSource === 'youtube' ? '' : 'hidden' ?>>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.youtube_url')) ?></td>
    <td class="<?= escape($tdClass) ?>">
        <input
            type="url"
            name="<?= escape($fieldPrefix) ?>youtube_url"
            value="<?= escape($form[$fieldPrefix . 'youtube_url'] ?? '') ?>"
            placeholder="https://www.youtube.com/watch?v=..."
            class="<?= escape($inputClass) ?>"
        >
        <?php if (!empty($errors['youtube_url'])): ?>
            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['youtube_url'][0]) ?></p>
        <?php endif; ?>
        <?php if ($existingYoutubeId && $selectedSource === 'youtube'): ?>
            <div class="mt-4 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-950 aspect-video max-w-xl">
                <iframe
                    class="w-full h-full"
                    src="https://www.youtube.com/embed/<?= escape($existingYoutubeId) ?>?rel=0"
                    title="<?= escape(__('courses.form.current_video')) ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
            <p class="mt-2 text-xs text-brand-600 dark:text-brand-500">
                <i class="fa-solid fa-circle-play mr-1"></i><?= escape(__('courses.form.current_video')) ?>
            </p>
        <?php endif; ?>
    </td>
</tr>
<tr class="video-field-row" data-prefix="<?= escape($fieldPrefix) ?>" data-source="upload" <?= $selectedSource === 'upload' ? '' : 'hidden' ?>>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.video_file')) ?></td>
    <td class="<?= escape($tdClass) ?>">
        <?php if ($existingVideoNugget && $selectedSource === 'upload' && !$existingVideoNugget->isYoutubeVideo() && $existingVideoNugget->contentUrl): ?>
            <div class="mb-3 p-3 rounded-xl bg-brand-500/10 border border-brand-500/20 text-sm">
                <p class="font-semibold text-brand-700 dark:text-brand-accent mb-1">
                    <i class="fa-solid fa-file-video mr-1"></i><?= escape(__('courses.form.current_video')) ?>
                </p>
                <p class="text-slate-700 dark:text-slate-300"><?= escape($existingVideoNugget->title) ?></p>
                <a href="<?= escape(url($existingVideoNugget->contentUrl)) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i><?= escape(__('courses.form.view_current_video')) ?>
                </a>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2"><?= escape(__('courses.form.replace_video_hint')) ?></p>
        <?php endif; ?>
        <input
            type="file"
            name="<?= escape($fieldPrefix) ?>video_file"
            accept="video/mp4,video/webm,.mp4,.webm"
            class="block w-full text-sm text-slate-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-500 file:text-slate-950 file:font-semibold hover:file:bg-brand-accent"
        >
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('courses.form.video_file_hint')) ?></p>
        <?php if (!empty($errors['video_file'])): ?>
            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['video_file'][0]) ?></p>
        <?php endif; ?>
    </td>
</tr>
<tr class="video-field-row" data-prefix="<?= escape($fieldPrefix) ?>" data-source="youtube upload" <?= in_array($selectedSource, ['youtube', 'upload'], true) ? '' : 'hidden' ?>>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.nugget_title')) ?></td>
    <td class="<?= escape($tdClass) ?>">
        <input
            type="text"
            name="<?= escape($fieldPrefix) ?>nugget_title"
            value="<?= escape($form[$fieldPrefix . 'nugget_title'] ?? '') ?>"
            placeholder="<?= escape(__('courses.form.nugget_title_placeholder')) ?>"
            class="<?= escape($inputClass) ?>"
        >
    </td>
</tr>
<?php if (($showModuleTitle ?? true) && $fieldPrefix === ''): ?>
<tr class="video-field-row" data-prefix="<?= escape($fieldPrefix) ?>" data-source="youtube upload" <?= in_array($selectedSource, ['youtube', 'upload'], true) ? '' : 'hidden' ?>>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.module_title')) ?></td>
    <td class="<?= escape($tdClass) ?>">
        <input
            type="text"
            name="module_title"
            value="<?= escape($form['module_title'] ?? '') ?>"
            placeholder="<?= escape(__('courses.form.module_title_placeholder')) ?>"
            class="<?= escape($inputClass) ?>"
        >
    </td>
</tr>
<?php endif; ?>
