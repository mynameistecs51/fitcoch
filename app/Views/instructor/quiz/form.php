<?php
$form = $form ?? [];
$errors = $errors ?? [];
$questionForm = $questionForm ?? [];
$questionsForm = $questionsForm ?? [];
$questionErrors = $questionErrors ?? [];
$importResult = $importResult ?? null;
$importError = $importError ?? null;
$inputClass = 'w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
$labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1';
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';

if ($questionsForm === []) {
    $questionsForm = [
        ['question_text' => '', 'option_1' => '', 'option_2' => '', 'option_3' => '', 'option_4' => '', 'correct_option' => 1, 'points' => 10],
        ['question_text' => '', 'option_1' => '', 'option_2' => '', 'option_3' => '', 'option_4' => '', 'correct_option' => 1, 'points' => 10],
    ];
}

$questionBlockError = static function (array $questionErrors, int $index, string $field): ?string {
    $key = 'questions.' . $index . '.' . $field;

    return $questionErrors[$key][0] ?? ($questionErrors[$field][0] ?? null);
};

ob_start();
?>
<section class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape(__('quizzes.instructor.editor_title')) ?></h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape($course->title) ?> — <?= escape($module->title) ?></p>
        </div>
        <a href="<?= escape(url('/instructor/courses/' . $course->id . '/edit')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('quizzes.instructor.back_to_course')) ?>
        </a>
    </div>

    <?php if ($success === 'quiz_saved'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm"><?= escape(__('quizzes.instructor.quiz_saved')) ?></div>
    <?php elseif ($success === 'quiz_deleted'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm"><?= escape(__('quizzes.instructor.quiz_deleted')) ?></div>
    <?php elseif ($success === 'questions_saved'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm"><?= escape(__('quizzes.instructor.questions_saved', ['count' => (int) ($_GET['count'] ?? 1)])) ?></div>
    <?php elseif ($success === 'question_saved'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm"><?= escape(__('quizzes.instructor.question_saved')) ?></div>
    <?php elseif ($success === 'question_deleted'): ?>
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm"><?= escape(__('quizzes.instructor.question_deleted')) ?></div>
    <?php endif; ?>

    <?php if ($error === 'csrf'): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm"><?= escape(__('errors.invalid_csrf')) ?></div>
    <?php elseif (is_string($error) && $error !== ''): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm"><?= escape($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($questionErrors['questions'])): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm"><?= escape($questionErrors['questions'][0]) ?></div>
    <?php endif; ?>

    <?php if (is_string($importError) && $importError !== ''): ?>
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-sm"><?= escape($importError) ?></div>
    <?php endif; ?>

    <?php if (is_array($importResult)): ?>
        <?php if (($importResult['created'] ?? 0) > 0): ?>
            <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-sm">
                <?= escape(__('quizzes.import.success', ['count' => (int) $importResult['created']])) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($importResult['errors'])): ?>
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-800 dark:text-amber-300 text-sm space-y-1">
                <p class="font-semibold"><?= escape(__('quizzes.import.partial_errors')) ?></p>
                <ul class="list-disc list-inside">
                    <?php foreach ($importResult['errors'] as $rowNumber => $message): ?>
                        <li><?= escape(__('quizzes.import.row_error', ['row' => (string) $rowNumber, 'message' => $message])) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('quizzes.instructor.quiz_settings')) ?></h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.quiz_title')) ?></label>
                    <input type="text" id="title" name="title" value="<?= escape($form['title'] ?? $quiz?->title ?? '') ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['title'])): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['title'][0]) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="passing_score_pct" class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.passing_score')) ?></label>
                    <input type="number" id="passing_score_pct" name="passing_score_pct" min="1" max="100" value="<?= escape((string) ($form['passing_score_pct'] ?? $quiz?->passingScorePct ?? 80)) ?>" required class="<?= escape($inputClass) ?>">
                    <?php if (!empty($errors['passing_score_pct'])): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($errors['passing_score_pct'][0]) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
                    <?= escape($quiz ? __('quizzes.instructor.save_quiz') : __('quizzes.instructor.create_quiz')) ?>
                </button>
                <?php if ($quiz): ?>
                    <a href="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/readiness')) ?>" class="px-6 py-3 border border-brand-500/30 text-brand-700 dark:text-brand-accent font-bold rounded-xl hover:bg-brand-500/10 transition">
                        <?= escape(__('quizzes.instructor.manage_readiness')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($quiz): ?>
            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz/' . $quiz->id . '/delete')) ?>" onsubmit="return confirm('<?= escape(__('quizzes.instructor.confirm_delete_quiz')) ?>');">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline font-semibold">
                    <i class="fa-solid fa-trash-can mr-1"></i><?= escape(__('quizzes.instructor.delete_quiz')) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($quiz): ?>
        <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-6">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('quizzes.instructor.questions')) ?></h2>

            <?php if ($questions === []): ?>
                <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('quizzes.instructor.no_questions')) ?></p>
            <?php else: ?>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr>
                                <th class="<?= escape($thClass) ?>">#</th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.question')) ?></th>
                                <th class="<?= escape($thClass) ?>"><?= escape(__('quizzes.instructor.points')) ?></th>
                                <th class="<?= escape($thClass) ?> text-right"><?= escape(__('live.table.actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <?php foreach ($questions as $index => $question): ?>
                                <tr>
                                    <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent"><?= escape((string) ($index + 1)) ?></td>
                                    <td class="<?= escape($tdClass) ?>">
                                        <p class="font-medium text-slate-900 dark:text-slate-200"><?= escape($question->questionText) ?></p>
                                        <ul class="mt-2 space-y-1 text-xs text-slate-500 dark:text-slate-400">
                                            <?php foreach ($question->options as $option): ?>
                                                <li>
                                                    <?= !empty($option['is_correct']) ? '<i class="fa-solid fa-check text-brand-500 mr-1"></i>' : '•' ?>
                                                    <?= escape($option['option_text']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td class="<?= escape($tdClass) ?>"><?= escape((string) $question->points) ?></td>
                                    <td class="<?= escape($tdClass) ?> text-right">
                                        <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz/' . $quiz->id . '/questions/' . $question->id . '/delete')) ?>" class="inline" onsubmit="return confirm('<?= escape(__('quizzes.instructor.confirm_delete_question')) ?>');">
                                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold"><?= escape(__('quizzes.instructor.delete_question')) ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="space-y-4 border-t border-slate-200 dark:border-slate-800 pt-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('quizzes.instructor.add_questions')) ?></h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('quizzes.instructor.add_questions_hint')) ?></p>
                </div>

                <div class="p-4 md:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('quizzes.import.title')) ?></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('quizzes.import.template_hint')) ?></p>
                    </div>
                    <a
                        href="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz/import/template')) ?>"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 dark:text-brand-500 hover:text-brand-accent transition"
                    >
                        <i class="fa-solid fa-file-excel"></i>
                        <?= escape(__('quizzes.import.download_template')) ?>
                    </a>
                    <form
                        method="POST"
                        action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz/' . $quiz->id . '/import')) ?>"
                        enctype="multipart/form-data"
                        class="space-y-3"
                    >
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <div>
                            <label for="quiz_file" class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.import.choose_file')) ?></label>
                            <input
                                type="file"
                                id="quiz_file"
                                name="quiz_file"
                                accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv"
                                required
                                class="<?= escape($inputClass) ?>"
                            >
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><?= escape(__('quizzes.import.file_hint')) ?></p>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 dark:bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-slate-700 transition text-sm">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <?= escape(__('quizzes.import.submit')) ?>
                        </button>
                    </form>
                </div>

            <form method="POST" action="<?= escape(url('/instructor/courses/' . $course->id . '/modules/' . $module->id . '/quiz/' . $quiz->id . '/questions')) ?>" id="quiz-questions-form" class="space-y-4"
                data-labels="<?= escape(json_encode([
                    'question_number' => __('quizzes.instructor.question_number'),
                    'question_text' => __('quizzes.instructor.question_text'),
                    'option' => __('quizzes.instructor.option'),
                    'correct_option' => __('quizzes.instructor.correct_option'),
                    'points' => __('quizzes.instructor.points'),
                    'remove_question' => __('quizzes.instructor.remove_question'),
                ], JSON_UNESCAPED_UNICODE)) ?>">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div id="question-blocks" class="space-y-4">
                    <?php foreach ($questionsForm as $qIndex => $qForm): ?>
                        <?php $selectedCorrect = (int) ($qForm['correct_option'] ?? 1); ?>
                        <div class="question-block rounded-2xl border border-slate-200 dark:border-slate-800 p-4 md:p-5 space-y-4 bg-slate-50/60 dark:bg-slate-950/40" data-index="<?= escape((string) $qIndex) ?>">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-bold text-slate-900 dark:text-white question-block-title"><?= escape(__('quizzes.instructor.question_number', ['number' => $qIndex + 1])) ?></p>
                                <button type="button" class="remove-question-block-btn text-xs text-red-600 dark:text-red-400 hover:underline font-semibold">
                                    <i class="fa-solid fa-xmark mr-1"></i><?= escape(__('quizzes.instructor.remove_question')) ?>
                                </button>
                            </div>

                            <div>
                                <label class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.question_text')) ?></label>
                                <textarea name="questions[<?= escape((string) $qIndex) ?>][question_text]" rows="2" class="<?= escape($inputClass) ?>"><?= escape($qForm['question_text'] ?? '') ?></textarea>
                                <?php $fieldError = $questionBlockError($questionErrors, (int) $qIndex, 'question_text'); ?>
                                <?php if ($fieldError): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($fieldError) ?></p><?php endif; ?>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <div>
                                        <label class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.option')) ?> <?= $i ?></label>
                                        <input type="text" name="questions[<?= escape((string) $qIndex) ?>][option_<?= $i ?>]" value="<?= escape($qForm['option_' . $i] ?? '') ?>" class="<?= escape($inputClass) ?>">
                                        <?php $fieldError = $questionBlockError($questionErrors, (int) $qIndex, 'option_' . $i); ?>
                                        <?php if ($fieldError): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($fieldError) ?></p><?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.correct_option')) ?></label>
                                    <select name="questions[<?= escape((string) $qIndex) ?>][correct_option]" class="<?= escape($inputClass) ?>">
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <option value="<?= $i ?>" <?= $selectedCorrect === $i ? 'selected' : '' ?>><?= escape(__('quizzes.instructor.option')) ?> <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <?php $fieldError = $questionBlockError($questionErrors, (int) $qIndex, 'correct_option'); ?>
                                    <?php if ($fieldError): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($fieldError) ?></p><?php endif; ?>
                                </div>
                                <div>
                                    <label class="<?= escape($labelClass) ?>"><?= escape(__('quizzes.instructor.points')) ?></label>
                                    <input type="number" name="questions[<?= escape((string) $qIndex) ?>][points]" min="1" max="100" value="<?= escape((string) ($qForm['points'] ?? 10)) ?>" class="<?= escape($inputClass) ?>">
                                    <?php $fieldError = $questionBlockError($questionErrors, (int) $qIndex, 'points'); ?>
                                    <?php if ($fieldError): ?><p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= escape($fieldError) ?></p><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-question-block-btn" class="w-full sm:w-auto px-4 py-2 border border-brand-500/30 text-brand-700 dark:text-brand-accent font-semibold rounded-xl hover:bg-brand-500/10 transition text-sm">
                    <i class="fa-solid fa-plus mr-1"></i><?= escape(__('quizzes.instructor.add_another_question')) ?>
                </button>

                <button type="submit" class="px-6 py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i><?= escape(__('quizzes.instructor.save_questions')) ?>
                </button>
            </form>
            </div>
        </div>
    <?php endif; ?>
</section>
<script src="<?= escape(url('/assets/quiz-question-form.js')) ?>"></script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'instructor';
require base_path('app/Views/layouts/app.php');
