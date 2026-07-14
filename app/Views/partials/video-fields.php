<?php
$fieldPrefix = $fieldPrefix ?? '';
$selectedSource = $form[$fieldPrefix . 'video_source'] ?? 'none';
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
    </td>
</tr>
<tr class="video-field-row" data-prefix="<?= escape($fieldPrefix) ?>" data-source="upload" <?= $selectedSource === 'upload' ? '' : 'hidden' ?>>
    <td class="<?= escape($labelTdClass) ?>"><?= escape(__('courses.form.video_file')) ?></td>
    <td class="<?= escape($tdClass) ?>">
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
