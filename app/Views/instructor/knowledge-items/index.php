<?php

$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$textareaClass = $inputClass . ' min-h-[120px] resize-y';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300 align-top';

$itemEditData = [];
foreach ($items as $item) {
    $itemEditData[$item->id] = [
        'concept_name' => $item->conceptName,
        'description' => $item->description ?? '',
    ];
}

ob_start();
?>
<section class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-brain text-brand-500"></i>
                <?= escape(__('knowledge_items.instructor.title')) ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape($course->title) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
                <?= escape(__('courses.instructor.edit')) ?>
            </a>
            <a href="<?= escape(url('/instructor/courses')) ?>" class="text-sm text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-500">
                <?= escape(__('courses.instructor.back')) ?>
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
            <?php if ($success === 'synced'): ?>
                <?= escape(__('knowledge_items.instructor.success.synced', ['count' => (string) ($syncCount ?? 0)])) ?>
            <?php else: ?>
                <?= escape(__('knowledge_items.instructor.success.' . $success)) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm">
            <?= escape($error === 'validation' ? __('errors.validation_failed') : ($error === 'csrf' ? __('errors.invalid_csrf') : (is_string($error) ? $error : __('errors.validation_failed')))) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('knowledge_items.instructor.create_hint')) ?></p>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/knowledge-items/sync')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold hover:border-brand-500/40 transition">
                        <i class="fa-solid fa-arrows-rotate text-brand-500"></i>
                        <?= escape(__('knowledge_items.instructor.sync_modules')) ?>
                    </button>
                </form>
                <button
                    type="button"
                    id="open-add-item-modal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20"
                >
                    <i class="fa-solid fa-plus"></i>
                    <?= escape(__('knowledge_items.instructor.create')) ?>
                </button>
            </div>
        </div>

        <?php if ($items === []): ?>
            <div class="py-10 text-center text-sm text-slate-500 dark:text-slate-400 border border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                <?= escape(__('knowledge_items.instructor.empty')) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('knowledge_items.form.concept_name')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('knowledge_items.form.description')) ?></th>
                            <th class="<?= escape($thClass) ?> text-right"><?= escape(__('courses.table.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200 whitespace-nowrap">
                                    <?= escape($item->conceptName) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> max-w-md">
                                    <p class="line-clamp-2 text-slate-600 dark:text-slate-300">
                                        <?= escape($item->description ?? '—') ?>
                                    </p>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="edit-item-btn text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent font-semibold"
                                        data-item-id="<?= escape((string) $item->id) ?>"
                                    >
                                        <?= escape(__('courses.instructor.edit')) ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<div id="add-item-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="add-item-modal-title">
    <div class="app-modal-backdrop" data-close-add-modal></div>
    <div class="app-modal-panel">
        <div class="app-modal-header">
            <h3 id="add-item-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">
                <?= escape(__('knowledge_items.instructor.create_title')) ?>
            </h3>
            <button type="button" class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" data-close-add-modal aria-label="<?= escape(__('courses.modal.cancel')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/knowledge-items')) ?>">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <div class="app-modal-body space-y-4">
                <div>
                    <label class="<?= escape($labelClass) ?>"><?= escape(__('knowledge_items.form.concept_name')) ?></label>
                    <input type="text" name="concept_name" required maxlength="100" class="<?= escape($inputClass) ?>" placeholder="<?= escape(__('knowledge_items.form.concept_placeholder')) ?>">
                </div>
                <div>
                    <label class="<?= escape($labelClass) ?>"><?= escape(__('knowledge_items.form.description')) ?></label>
                    <textarea name="description" class="<?= escape($textareaClass) ?>" placeholder="<?= escape(__('knowledge_items.form.description_placeholder')) ?>"></textarea>
                </div>
            </div>
            <div class="app-modal-footer">
                <button type="button" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-close-add-modal>
                    <?= escape(__('courses.modal.cancel')) ?>
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                    <?= escape(__('knowledge_items.instructor.create')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="edit-item-modal" class="app-modal hidden" role="dialog" aria-modal="true" aria-labelledby="edit-item-modal-title">
    <div class="app-modal-backdrop" data-close-edit-modal></div>
    <div class="app-modal-panel">
        <div class="app-modal-header">
            <h3 id="edit-item-modal-title" class="text-lg font-bold text-slate-900 dark:text-white">
                <?= escape(__('knowledge_items.instructor.edit_title')) ?>
            </h3>
            <button type="button" class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" data-close-edit-modal aria-label="<?= escape(__('courses.modal.cancel')) ?>">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="" id="edit-item-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <div class="app-modal-body space-y-4">
                <div>
                    <label class="<?= escape($labelClass) ?>"><?= escape(__('knowledge_items.form.concept_name')) ?></label>
                    <input type="text" name="concept_name" id="edit-item-concept" required maxlength="100" class="<?= escape($inputClass) ?>">
                </div>
                <div>
                    <label class="<?= escape($labelClass) ?>"><?= escape(__('knowledge_items.form.description')) ?></label>
                    <textarea name="description" id="edit-item-description" class="<?= escape($textareaClass) ?>" placeholder="<?= escape(__('knowledge_items.form.description_placeholder')) ?>"></textarea>
                </div>
            </div>
            <div class="app-modal-footer flex-wrap gap-2">
                <button type="button" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-close-edit-modal>
                    <?= escape(__('courses.modal.cancel')) ?>
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                    <?= escape(__('knowledge_items.instructor.save')) ?>
                </button>
                <button type="submit" form="delete-item-form" class="ml-auto px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 hover:underline" onclick="return confirm('<?= escape(__('knowledge_items.instructor.confirm_delete')) ?>');">
                    <?= escape(__('knowledge_items.instructor.delete')) ?>
                </button>
            </div>
        </form>
        <form method="POST" action="" id="delete-item-form" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        </form>
    </div>
</div>

<script type="application/json" id="item-edit-data"><?= json_encode($itemEditData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script>
(function () {
    const courseId = <?= (int) $course->id ?>;
    const updateBase = <?= json_encode(url('/instructor/courses/' . $course->id . '/knowledge-items/'), JSON_UNESCAPED_UNICODE) ?>;

    function bindModal(modalId, openSelector, closeAttr) {
        const modal = document.getElementById(modalId);
        if (!modal) return { open: () => {}, close: () => {} };

        const open = () => {
            modal.classList.remove('hidden');
            document.body.classList.add('app-modal-open');
        };
        const close = () => {
            modal.classList.add('hidden');
            document.body.classList.remove('app-modal-open');
        };

        if (openSelector) {
            const openBtn = document.querySelector(openSelector);
            openBtn?.addEventListener('click', open);
        }

        modal.querySelectorAll('[' + closeAttr + ']').forEach((el) => {
            el.addEventListener('click', close);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                close();
            }
        });

        return { open, close };
    }

    bindModal('add-item-modal', '#open-add-item-modal', 'data-close-add-modal');

    const editModal = bindModal('edit-item-modal', null, 'data-close-edit-modal');
    const editForm = document.getElementById('edit-item-form');
    const deleteForm = document.getElementById('delete-item-form');
    const dataEl = document.getElementById('item-edit-data');

    if (!editForm || !deleteForm || !dataEl) {
        return;
    }

    let itemData = {};
    try {
        itemData = JSON.parse(dataEl.textContent || '{}');
    } catch (error) {
        itemData = {};
    }

    document.querySelectorAll('.edit-item-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const itemId = btn.getAttribute('data-item-id');
            if (!itemId || !itemData[itemId]) {
                return;
            }

            const row = itemData[itemId];
            editForm.action = updateBase + itemId;
            deleteForm.action = updateBase + itemId + '/delete';
            document.getElementById('edit-item-concept').value = row.concept_name || '';
            document.getElementById('edit-item-description').value = row.description || '';
            editModal.open();
        });
    });
})();
</script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
