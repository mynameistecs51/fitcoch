<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\DiscussionService;
use App\Services\LessonNavigationService;
use App\Services\NuggetService;
use App\Services\QuizService;
use App\Services\ValidationException;
use Exception;

class NuggetController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly NuggetService $nuggetService,
        private readonly LessonNavigationService $lessonNavigationService,
        private readonly DiscussionService $discussionService,
        private readonly QuizService $quizService,
    ) {
    }

    public function show(Request $request, int $nuggetId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $lesson = $this->nuggetService->getLessonForLearner($nuggetId, $user->id);

        if ($lesson === null) {
            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ]);
        }

        $roles = $this->authService->getUserRoles($user->id);
        $lessonNav = $this->lessonNavigationService->buildForLearner(
            $lesson['course']->id,
            $user->id,
            $lesson['module']->id,
            $nuggetId,
        );
        $moduleQuizData = $this->resolveModuleQuizData($lesson['module']->id, $user->id, $request);

        return Response::view('courses/nugget', array_merge([
            'title' => $lesson['nugget']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'nugget' => $lesson['nugget'],
            'module' => $lesson['module'],
            'course' => $lesson['course'],
            'progress' => $lesson['progress'],
            'youtubeId' => $lesson['youtube_id'],
            'streamUrl' => $lesson['stream_url'],
            'lessonNav' => $lessonNav,
            'moduleQuiz' => $moduleQuizData['quiz'],
            'moduleQuizQuestions' => $moduleQuizData['questions'],
            'moduleQuizTicket' => $moduleQuizData['ticket'],
            'moduleQuizLatestAttempt' => $moduleQuizData['latest_attempt'],
            'moduleQuizRetake' => ($request->query()['retake'] ?? '') === '1',
            'moduleQuizRetakeUrl' => url('/nuggets/' . $nuggetId . '?retake=1'),
            'moduleQuizResult' => null,
            'moduleQuizError' => null,
        ], $this->discussionService->buildViewContext(
            $request,
            $lesson['module']->id,
            $user->id,
            '/nuggets/' . $nuggetId,
        )));
    }

    /** @return array{quiz: ?\App\Models\Quiz, questions: array<int, \App\Models\Question>, ticket: ?\App\Models\ReadinessTicket, latest_attempt: ?array<string, mixed>} */
    private function resolveModuleQuizData(int $moduleId, int $userId, Request $request): array
    {
        $quizzes = $this->quizService->listQuizzesByModuleIds([$moduleId]);
        $quiz = $quizzes[$moduleId] ?? null;

        if ($quiz === null) {
            return [
                'quiz' => null,
                'questions' => [],
                'ticket' => null,
                'latest_attempt' => null,
            ];
        }

        $quizData = $this->quizService->getQuizForLearner($quiz->id, $userId);

        if ($quizData === null) {
            return [
                'quiz' => null,
                'questions' => [],
                'ticket' => null,
                'latest_attempt' => null,
            ];
        }

        return [
            'quiz' => $quizData['quiz'],
            'questions' => $quizData['questions'],
            'ticket' => $quizData['ticket'],
            'latest_attempt' => $quizData['latest_attempt'],
        ];
    }

    public function stream(Request $request, int $nuggetId): Response
    {
        $userId = (int) ($request->getAttribute('user_id') ?? 0);

        if ($userId <= 0) {
            $user = $this->authService->currentUser();
            $userId = $user?->id ?? 0;
        }

        if ($userId <= 0) {
            http_response_code(401);
            exit;
        }

        $this->nuggetService->streamNuggetVideo($nuggetId, $userId);

        return new Response();
    }

    public function apiShow(Request $request, int $nuggetId): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $lesson = $this->nuggetService->getLessonForLearner($nuggetId, $userId);

        if ($lesson === null) {
            return Response::apiError('FORBIDDEN', __('errors.forbidden'), 403);
        }

        return Response::apiSuccess([
            'nugget' => $lesson['nugget']->toArray(),
            'module' => $lesson['module']->toArray(),
            'course' => $lesson['course']->toArray(),
            'progress' => $lesson['progress'],
            'youtube_id' => $lesson['youtube_id'],
            'stream_url' => $lesson['stream_url'],
        ]);
    }

    public function apiProgress(Request $request, int $nuggetId): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        try {
            $payload = $request->isApi() ? $request->json() : $request->all();
            $data = $this->nuggetService->updateProgress($nuggetId, $userId, $payload);
        } catch (ValidationException $e) {
            return Response::apiError('VALIDATION_FAILED', $e->getMessage(), 422, $e->errors());
        } catch (Exception $e) {
            return Response::apiError('FORBIDDEN', $e->getMessage(), 403);
        }

        return Response::apiSuccess($data);
    }
}
