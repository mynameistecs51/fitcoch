<?php

$inputClass = 'ux-input';
$textareaClass = 'ux-input min-h-[120px] resize-y';
$labelClass = 'ux-label';
$instructorQuickLinkClass = 'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-brand-500/30 hover:text-brand-600 dark:hover:text-brand-accent transition';
$actionBtnClass = $instructorQuickLinkClass;

$itemEditData = [];
foreach ($items as $item) {
    $itemEditData[$item->id] = [
        'concept_name' => $item->conceptName,
        'description' => $item->description ?? '',
    ];
}

$heroTitle = __('knowledge_items.instructor.title');
$heroSubtitle = $course->title;
$heroBadgeIcon = 'fa-brain';
ob_start();
?>
<a href="<?= escape(url('/instructor/courses/' . $course->id . '/progress')) ?>" class="<?= escape($instructorQuickLinkClass) ?>">
    <i class="fa-solid fa-chart-line text-brand-500"></i>
    <?= escape(__('courses.instructor.view_progress')) ?>
</a>
<a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="<?= escape($instructorQuickLinkClass) ?>">
    <i class="fa-solid fa-pen-to-square text-violet-500"></i>
    <?= escape(__('courses.instructor.edit')) ?>
</a>
<a href="<?= escape(url('/instructor/courses')) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition">
    <i class="fa-solid fa-arrow-left text-xs"></i>
    <?= escape(__('courses.instructor.back')) ?>
</a>
<?php
$heroActions = ob_get_clean();

ob_start();
?>
<section class="space-y-8">
    <?php require base_path('app/Views/partials/instructor-page-hero.php'); ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
        <div class="ux-stat-card ux-card p-5 md:p-6">
            <div class="ux-stat-icon bg-brand-500/10 text-brand-600 dark:text-brand-accent">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 font-semibold"><?= escape(__('knowledge_items.instructor.title')) ?></p>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-600 dark:text-brand-accent mt-1"><?= escape((string) count($items)) ?></p>
        </div>
        <div class="ux-stat-card ux-card p-5 md:p-6 border-brand-500/20 bg-gradient-to-br from-brand-500/8 to-transparent">
            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?= escape(__('knowledge_items.instructor.create_hint')) ?></p>
            <div class="flex flex-wrap items-center gap-2 mt-4">
                <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/knowledge-items/sync')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button type="submit" class="<?= escape($actionBtnClass) ?>">
                        <i class="fa-solid fa-arrows-rotate text-brand-500"></i>
                        <?= escape(__('knowledge_items.instructor.sync_modules')) ?>
                    </button>
                </form>
                <button type="button" id="open-add-item-modal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-plus"></i>
                    <?= escape(__('knowledge_items.instructor.create')) ?>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="ux-alert-enter p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
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

    <div class="ux-card p-6 md:p-8 space-y-5">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-list text-brand-500"></i>
            <?= escape(__('knowledge_items.instructor.title')) ?>
        </h2>

        <?php if ($items === []): ?>
            <div class="flex flex-col items-center justify-center text-center px-4 py-14 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-950/40">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-accent mb-4">
                    <i class="fa-solid fa-brain text-xl"></i>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm"><?= escape(__('knowledge_items.instructor.empty')) ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($items as $item): ?>
                    <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40 p-5 flex flex-col justify-between gap-4 min-h-[160px] hover:border-brand-500/25 transition">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= escape($item->conceptName) ?></h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 line-clamp-3 leading-relaxed">
                                <?= escape($item->description !== null && $item->description !== '' ? $item->description : '—') ?>
                            </p>
                        </div>
                        <button
                            type="button"
                            class="edit-item-btn self-start <?= escape($actionBtnClass) ?>"
                            data-item-id="<?= escape((string) $item->id) ?>"
                        >
                            <i class="fa-solid fa-pen-to-square text-violet-500"></i>
                            <?= escape(__('courses.instructor.edit')) ?>
                        </button>
                    </article>
                <?php endforeach; ?>
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
