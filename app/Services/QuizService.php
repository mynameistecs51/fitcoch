<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\Quiz;
use App\Models\ReadinessTicket;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\ReadinessTicketRepository;
use Exception;

class QuizService
{
    public function __construct(
        private readonly QuizRepository $quizRepo,
        private readonly QuizAttemptRepository $attemptRepo,
        private readonly ReadinessTicketRepository $ticketRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly CourseRepository $courseRepo,
        private readonly CohortRepository $cohortRepo,
        private readonly LessonUnlockService $unlockService,
        private readonly GamificationService $gamificationService,
    ) {
    }

    /**
     * @return array{
     *     quiz: Quiz,
     *     module: Module,
     *     questions: array<int, \App\Models\Question>,
     *     ticket: ?ReadinessTicket,
     *     course: \App\Models\Course,
     *     latest_attempt: ?array{
     *         attempt_id: int,
     *         score_pct: int,
     *         passed: bool,
     *         completed_at: string,
     *         responses: array<int, int>
     *     }
     * }|null
     */
    public function getQuizForLearner(int $quizId, int $userId): ?array
    {
        $quiz = $this->quizRepo->findById($quizId);

        if ($quiz === null) {
            return null;
        }

        $context = $this->resolveLearnerModuleContext($quiz->moduleId, $userId);

        if ($context === null) {
            return null;
        }

        return [
            'quiz' => $quiz,
            'module' => $context['module'],
            'questions' => $this->quizRepo->listQuestionsWithOptions($quizId, false),
            'ticket' => $context['ticket'],
            'course' => $context['course'],
            'latest_attempt' => $this->buildLatestAttemptReview($quizId, $userId, $quiz->passingScorePct),
        ];
    }

    /**
     * @return array{
     *     attempt_id: int,
     *     score_pct: int,
     *     passed: bool,
     *     completed_at: string,
     *     responses: array<int, int>
     * }|null
     */
    public function buildLatestAttemptReview(int $quizId, int $userId, int $passingScorePct): ?array
    {
        $latestAttempts = $this->attemptRepo->findLatestByUserAndQuizIds($userId, [$quizId]);
        $attempt = $latestAttempts[$quizId] ?? null;

        if ($attempt === null) {
            return null;
        }

        return [
            'attempt_id' => $attempt['id'],
            'score_pct' => $attempt['score_pct'],
            'passed' => $attempt['score_pct'] >= $passingScorePct,
            'completed_at' => $attempt['completed_at'],
            'responses' => $this->attemptRepo->findResponsesByAttemptId($attempt['id']),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function submitAttempt(int $quizId, int $userId, array $payload): array
    {
        $quiz = $this->quizRepo->findById($quizId);

        if ($quiz === null) {
            throw new Exception(__('quizzes.validation.not_found'));
        }

        $context = $this->resolveLearnerModuleContext($quiz->moduleId, $userId);

        if ($context === null) {
            throw new Exception(__('errors.forbidden'));
        }

        $questions = $this->quizRepo->listQuestionsWithOptions($quizId, true);
        $responses = $this->normalizeResponses($payload, $questions);
        $scorePct = $this->calculateScorePct($questions, $responses);
        $hadPassedBefore = $this->attemptRepo->hasPassingAttempt($userId, $quizId, $quiz->passingScorePct);
        $attempt = $this->attemptRepo->create($userId, $quizId, $scorePct, $responses);
        $passed = $scorePct >= $quiz->passingScorePct;
        $ticket = $context['ticket'];
        $xpAwarded = 0;

        if ($passed && !$hadPassedBefore) {
            $gamification = $this->gamificationService->recordQuizPassed(
                $userId,
                $scorePct,
                $quiz->quizType,
            );
            $xpAwarded = $gamification['xp_awarded'] ?? 0;
        }

        if ($quiz->quizType === 'readiness' && $passed) {
            $ticket = $this->ticketRepo->unlock(
                $userId,
                $context['cohort']->id,
                $quiz->moduleId
            );
        } elseif ($ticket === null) {
            $ticket = $this->ticketRepo->ensureLocked(
                $userId,
                $context['cohort']->id,
                $quiz->moduleId
            );
        }

        return [
            'attempt_id' => $attempt['id'],
            'score_pct' => $scorePct,
            'passed' => $passed,
            'readiness_ticket' => $ticket->toArray(),
            'xp_awarded' => $xpAwarded,
        ];
    }

    /**
     * @return array{
     *     course: \App\Models\Course,
     *     cohort: \App\Models\Cohort,
     *     module: Module,
     *     quiz: ?Quiz,
     *     tickets: array<int, array<string, mixed>>
     * }|null
     */
    public function getInstructorReadinessPanel(int $courseId, int $moduleId): ?array
    {
        $course = $this->courseRepo->findById($courseId);
        $module = $this->moduleRepo->findById($moduleId);

        if ($course === null || $module === null || $module->courseId !== $courseId) {
            return null;
        }

        $cohorts = $this->cohortRepo->listByCourseId($courseId);
        $cohort = $cohorts[0] ?? null;

        if ($cohort === null) {
            return null;
        }

        $quizzesByModule = $this->quizRepo->listByModuleIds([$moduleId]);
        $quiz = $quizzesByModule[$moduleId] ?? $this->quizRepo->findReadinessByModuleId($moduleId);
        $tickets = $this->ticketRepo->listEnrollmentStatuses($cohort->id, $moduleId);

        if ($quiz !== null) {
            $latestAttempts = $this->attemptRepo->findLatestByCohortAndQuizIds($cohort->id, [$quiz->id]);

            foreach ($tickets as $index => $row) {
                $userId = (int) $row['user_id'];
                $attempt = $latestAttempts[$userId][$quiz->id] ?? null;
                $latestScore = $attempt['score_pct'] ?? null;
                $tickets[$index]['latest_score'] = $latestScore;
                $tickets[$index]['quiz_passed'] = $latestScore !== null && $latestScore >= $quiz->passingScorePct;
            }
        }

        return [
            'course' => $course,
            'cohort' => $cohort,
            'module' => $module,
            'quiz' => $quiz,
            'tickets' => $tickets,
        ];
    }

    public function lockTicket(int $courseId, int $moduleId, int $learnerId): ReadinessTicket
    {
        $panel = $this->getInstructorReadinessPanel($courseId, $moduleId);

        if ($panel === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $enrolled = false;
        foreach ($panel['tickets'] as $ticketRow) {
            if ((int) $ticketRow['user_id'] === $learnerId) {
                $enrolled = true;
                break;
            }
        }

        if (!$enrolled) {
            throw new Exception(__('quizzes.validation.learner_not_enrolled'));
        }

        return $this->ticketRepo->lock(
            $learnerId,
            $panel['cohort']->id,
            $moduleId
        );
    }

    public function overrideTicket(int $courseId, int $moduleId, int $learnerId, int $instructorId): ReadinessTicket
    {
        $panel = $this->getInstructorReadinessPanel($courseId, $moduleId);

        if ($panel === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $enrolled = false;
        foreach ($panel['tickets'] as $ticketRow) {
            if ((int) $ticketRow['user_id'] === $learnerId) {
                $enrolled = true;
                break;
            }
        }

        if (!$enrolled) {
            throw new Exception(__('quizzes.validation.learner_not_enrolled'));
        }

        return $this->ticketRepo->override(
            $learnerId,
            $panel['cohort']->id,
            $moduleId,
            $instructorId
        );
    }

    /**
     * @param array<int, int> $moduleIds
     * @return array<int, Quiz>
     */
    public function listQuizzesByModuleIds(array $moduleIds): array
    {
        return $this->quizRepo->listByModuleIds($moduleIds);
    }

    /**
     * @return array<int, ReadinessTicket|null>
     */
    public function listTicketsForModules(int $userId, int $cohortId, array $moduleIds): array
    {
        $tickets = [];

        foreach ($moduleIds as $moduleId) {
            $tickets[$moduleId] = $this->ticketRepo->find($userId, $cohortId, $moduleId);
        }

        return $tickets;
    }

    /**
     * @return array{
     *     course: \App\Models\Course,
     *     module: Module,
     *     quiz: ?Quiz,
     *     questions: array<int, Question>
     * }|null
     */
    public function getQuizEditor(int $courseId, int $moduleId): ?array
    {
        $course = $this->courseRepo->findById($courseId);
        $module = $this->moduleRepo->findById($moduleId);

        if ($course === null || $module === null || $module->courseId !== $courseId) {
            return null;
        }

        $quiz = $this->quizRepo->findReadinessByModuleId($moduleId);

        return [
            'course' => $course,
            'module' => $module,
            'quiz' => $quiz,
            'questions' => $quiz ? $this->quizRepo->listQuestionsWithOptions($quiz->id, true) : [],
        ];
    }

    /** @param array<string, mixed> $data */
    public function saveQuiz(int $courseId, int $moduleId, array $data): Quiz
    {
        $editor = $this->getQuizEditor($courseId, $moduleId);

        if ($editor === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateQuizInput($data);
        $existing = $editor['quiz'];

        if ($existing !== null) {
            return $this->quizRepo->update($existing->id, $validated);
        }

        return $this->quizRepo->create([
            'module_id' => $moduleId,
            'quiz_type' => 'readiness',
            'title' => $validated['title'],
            'passing_score_pct' => $validated['passing_score_pct'],
        ]);
    }

    public function deleteQuiz(int $courseId, int $moduleId, int $quizId): void
    {
        $editor = $this->getQuizEditor($courseId, $moduleId);

        if ($editor === null || $editor['quiz'] === null || $editor['quiz']->id !== $quizId) {
            throw new Exception(__('quizzes.validation.not_found'));
        }

        $this->quizRepo->delete($quizId);
    }

    /** @param array<string, mixed> $data */
    public function saveQuestion(int $courseId, int $moduleId, int $quizId, array $data): Question
    {
        $editor = $this->assertQuizEditor($courseId, $moduleId, $quizId);
        $validated = $this->validateQuestionInput($data);
        $questionId = (int) ($data['question_id'] ?? 0);

        if ($questionId > 0) {
            $question = $this->quizRepo->findQuestionById($questionId);

            if ($question === null || $question->quizId !== $quizId) {
                throw new Exception(__('quizzes.validation.question_not_found'));
            }

            $question = $this->quizRepo->updateQuestion($questionId, [
                'question_text' => $validated['question_text'],
                'points' => $validated['points'],
            ]);
        } else {
            $question = $this->quizRepo->createQuestion([
                'quiz_id' => $quizId,
                'question_text' => $validated['question_text'],
                'question_type' => 'single_choice',
                'points' => $validated['points'],
            ]);
        }

        $this->quizRepo->syncQuestionOptions($question->id, $validated['options']);

        return $this->quizRepo->findQuestionById($question->id)
            ?? throw new Exception(__('quizzes.validation.question_not_found'));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, Question>
     */
    public function saveQuestions(int $courseId, int $moduleId, int $quizId, array $data): array
    {
        $this->assertQuizEditor($courseId, $moduleId, $quizId);
        $rawQuestions = $data['questions'] ?? [];

        if (!is_array($rawQuestions)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'questions' => [__('quizzes.validation.questions_required')],
            ]);
        }

        $blocks = [];
        $errors = [];

        foreach ($rawQuestions as $index => $questionData) {
            if (!is_array($questionData) || $this->isQuestionBlockEmpty($questionData)) {
                continue;
            }

            $blockErrors = $this->collectQuestionInputErrors($questionData, (string) $index);

            if ($blockErrors !== []) {
                $errors = array_merge($errors, $blockErrors);
                continue;
            }

            $blocks[] = $this->normalizeQuestionInput($questionData);
        }

        if ($blocks === [] && $errors === []) {
            throw new ValidationException(__('errors.validation_failed'), [
                'questions' => [__('quizzes.validation.questions_required')],
            ]);
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        $created = [];

        foreach ($blocks as $validated) {
            $question = $this->quizRepo->createQuestion([
                'quiz_id' => $quizId,
                'question_text' => $validated['question_text'],
                'question_type' => 'single_choice',
                'points' => $validated['points'],
            ]);
            $this->quizRepo->syncQuestionOptions($question->id, $validated['options']);
            $created[] = $this->quizRepo->findQuestionById($question->id)
                ?? throw new Exception(__('quizzes.validation.question_not_found'));
        }

        return $created;
    }

    public function deleteQuestion(int $courseId, int $moduleId, int $quizId, int $questionId): void
    {
        $this->assertQuizEditor($courseId, $moduleId, $quizId);
        $question = $this->quizRepo->findQuestionById($questionId);

        if ($question === null || $question->quizId !== $quizId) {
            throw new Exception(__('quizzes.validation.question_not_found'));
        }

        $this->quizRepo->deleteQuestion($questionId);
    }

    /**
     * @return array{course: \App\Models\Course, module: Module, quiz: Quiz}
     */
    private function assertQuizEditor(int $courseId, int $moduleId, int $quizId): array
    {
        $editor = $this->getQuizEditor($courseId, $moduleId);

        if ($editor === null || $editor['quiz'] === null || $editor['quiz']->id !== $quizId) {
            throw new Exception(__('quizzes.validation.not_found'));
        }

        return [
            'course' => $editor['course'],
            'module' => $editor['module'],
            'quiz' => $editor['quiz'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function validateQuizInput(array $data): array
    {
        $errors = [];
        $title = trim((string) ($data['title'] ?? ''));
        $passingScore = (int) ($data['passing_score_pct'] ?? 80);

        if ($title === '') {
            $errors['title'][] = __('quizzes.validation.title_required');
        }

        if ($passingScore < 1 || $passingScore > 100) {
            $errors['passing_score_pct'][] = __('quizzes.validation.passing_score_invalid');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'title' => $title,
            'passing_score_pct' => $passingScore,
        ];
    }

    /** @param array<string, mixed> $data */
    private function validateQuestionInput(array $data): array
    {
        $errors = $this->collectQuestionInputErrors($data, '');

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return $this->normalizeQuestionInput($data);
    }

    /** @param array<string, mixed> $data */
    private function isQuestionBlockEmpty(array $data): bool
    {
        if (trim((string) ($data['question_text'] ?? '')) !== '') {
            return false;
        }

        for ($i = 1; $i <= 4; $i++) {
            if (trim((string) ($data['option_' . $i] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, array<int, string>>
     */
    private function collectQuestionInputErrors(array $data, string $index): array
    {
        $errors = [];
        $prefix = $index === '' ? '' : 'questions.' . $index . '.';
        $questionText = trim((string) ($data['question_text'] ?? ''));
        $points = (int) ($data['points'] ?? 10);
        $correctOption = (int) ($data['correct_option'] ?? 0);

        if ($questionText === '') {
            $errors[$prefix . 'question_text'][] = __('quizzes.validation.question_text_required');
        }

        if ($points < 1 || $points > 100) {
            $errors[$prefix . 'points'][] = __('quizzes.validation.points_invalid');
        }

        for ($i = 1; $i <= 4; $i++) {
            $text = trim((string) ($data['option_' . $i] ?? ''));

            if ($text === '') {
                $errors[$prefix . 'option_' . $i][] = __('quizzes.validation.option_required');
            }
        }

        if ($correctOption < 1 || $correctOption > 4) {
            $errors[$prefix . 'correct_option'][] = __('quizzes.validation.correct_option_required');
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{question_text: string, points: int, options: array<int, array{option_number: int, option_text: string, is_correct: bool}>}
     */
    private function normalizeQuestionInput(array $data): array
    {
        $questionText = trim((string) ($data['question_text'] ?? ''));
        $points = (int) ($data['points'] ?? 10);
        $correctOption = (int) ($data['correct_option'] ?? 1);
        $options = [];

        for ($i = 1; $i <= 4; $i++) {
            $options[] = [
                'option_number' => $i,
                'option_text' => trim((string) ($data['option_' . $i] ?? '')),
                'is_correct' => $correctOption === $i,
            ];
        }

        return [
            'question_text' => $questionText,
            'points' => $points,
            'options' => $options,
        ];
    }

    /**
     * @return array{
     *     course: \App\Models\Course,
     *     cohort: \App\Models\Cohort,
     *     module: Module,
     *     ticket: ?ReadinessTicket
     * }|null
     */
    private function resolveLearnerModuleContext(int $moduleId, int $userId): ?array
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null) {
            return null;
        }

        $course = $this->courseRepo->findById($module->courseId);

        if ($course === null || $course->status !== 'published') {
            return null;
        }

        if (!$this->courseRepo->isUserEnrolled($userId, $course->id)) {
            return null;
        }

        if (!$this->unlockService->canAccessModule($moduleId, $userId)) {
            return null;
        }

        $cohort = $this->cohortRepo->findActiveEnrollmentForUser($userId, $course->id);

        if ($cohort === null) {
            return null;
        }

        $ticket = $this->ticketRepo->find($userId, $cohort->id, $moduleId);

        return [
            'course' => $course,
            'cohort' => $cohort,
            'module' => $module,
            'ticket' => $ticket,
        ];
    }

    /**
     * @param array<int, \App\Models\Question> $questions
     * @param array<string, mixed> $payload
     * @return array<int, array{question_id: int, selected_option_number: int}>
     */
    private function normalizeResponses(array $payload, array $questions): array
    {
        $errors = [];
        $rawResponses = $payload['responses'] ?? $payload;

        if (!is_array($rawResponses)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'responses' => [__('quizzes.validation.responses_required')],
            ]);
        }

        $indexed = [];
        foreach ($rawResponses as $key => $response) {
            if (is_array($response)) {
                $questionId = (int) ($response['question_id'] ?? 0);
                $optionNumber = (int) ($response['selected_option_number'] ?? 0);

                if ($questionId > 0) {
                    $indexed[$questionId] = $optionNumber;
                }
                continue;
            }

            if (is_numeric($key)) {
                $indexed[(int) $key] = (int) $response;
            }
        }

        $normalized = [];
        foreach ($questions as $question) {
            $selected = $indexed[$question->id] ?? 0;

            if ($selected <= 0) {
                $errors['responses'][] = __('quizzes.validation.answer_required');
                continue;
            }

            $validOption = false;
            foreach ($question->options as $option) {
                if ($option['option_number'] === $selected) {
                    $validOption = true;
                    break;
                }
            }

            if (!$validOption) {
                $errors['responses'][] = __('quizzes.validation.invalid_option');
                continue;
            }

            $normalized[] = [
                'question_id' => $question->id,
                'selected_option_number' => $selected,
            ];
        }

        if ($errors !== [] || count($normalized) !== count($questions)) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return $normalized;
    }

    /**
     * @param array<int, \App\Models\Question> $questions
     * @param array<int, array{question_id: int, selected_option_number: int}> $responses
     */
    private function calculateScorePct(array $questions, array $responses): int
    {
        $responsesByQuestion = [];
        foreach ($responses as $response) {
            $responsesByQuestion[$response['question_id']] = $response['selected_option_number'];
        }

        $earned = 0;
        $total = 0;

        foreach ($questions as $question) {
            $total += $question->points;
            $selected = $responsesByQuestion[$question->id] ?? 0;

            foreach ($question->options as $option) {
                if ($option['option_number'] === $selected && !empty($option['is_correct'])) {
                    $earned += $question->points;
                    break;
                }
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($earned / $total) * 100);
    }
}
