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
    ) {
    }

    /**
     * @return array{
     *     quiz: Quiz,
     *     module: Module,
     *     questions: array<int, \App\Models\Question>,
     *     ticket: ?ReadinessTicket
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
        $attempt = $this->attemptRepo->create($userId, $quizId, $scorePct, $responses);
        $passed = $scorePct >= $quiz->passingScorePct;
        $ticket = $context['ticket'];

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
            'xp_awarded' => $passed ? 150 : 0,
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

        return [
            'course' => $course,
            'cohort' => $cohort,
            'module' => $module,
            'quiz' => $this->quizRepo->findReadinessByModuleId($moduleId),
            'tickets' => $this->ticketRepo->listEnrollmentStatuses($cohort->id, $moduleId),
        ];
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
