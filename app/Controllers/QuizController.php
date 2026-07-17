<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\NuggetRepository;
use App\Services\AuthService;
use App\Services\DiscussionService;
use App\Services\LessonNavigationService;
use App\Services\QuizService;
use App\Services\ValidationException;
use Exception;

class QuizController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly QuizService $quizService,
        private readonly LessonNavigationService $lessonNavigationService,
        private readonly DiscussionService $discussionService,
        private readonly NuggetRepository $nuggetRepo,
    ) {
    }

    public function show(Request $request, int $quizId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $quizData = $this->quizService->getQuizForLearner($quizId, $user->id);

        if ($quizData === null) {
            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ]);
        }

        $roles = $this->authService->getUserRoles($user->id);
        $lessonNav = $this->lessonNavigationService->buildForLearner(
            $quizData['course']->id,
            $user->id,
            $quizData['module']->id,
            null,
            $quizId,
        );

        return Response::view('courses/quiz', array_merge([
            'title' => $quizData['quiz']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'quiz' => $quizData['quiz'],
            'module' => $quizData['module'],
            'course' => $quizData['course'],
            'questions' => $quizData['questions'],
            'ticket' => $quizData['ticket'],
            'latestAttempt' => $quizData['latest_attempt'] ?? null,
            'retake' => ($request->query()['retake'] ?? '') === '1',
            'retakeQuizUrl' => url('/quizzes/' . $quizId . '?retake=1'),
            'result' => null,
            'error' => $request->query()['error'] ?? null,
            'lessonNav' => $lessonNav,
            'retakeLessonUrl' => $this->resolveModuleVideoUrl($quizData['module']->id),
        ], $this->discussionService->buildViewContext(
            $request,
            $quizData['module']->id,
            $user->id,
            '/quizzes/' . $quizId,
        )));
    }

    private function resolveModuleVideoUrl(int $moduleId): ?string
    {
        foreach ($this->nuggetRepo->listByModuleId($moduleId) as $nugget) {
            if ($nugget->nuggetType === 'video') {
                return url('/nuggets/' . $nugget->id);
            }
        }

        return null;
    }

    public function submit(Request $request, int $quizId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/quizzes/' . $quizId . '?error=csrf');
        }

        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $result = $this->quizService->submitAttempt($quizId, $user->id, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/quizzes/' . $quizId . '?error=validation');
        } catch (Exception $e) {
            return Response::redirect('/quizzes/' . $quizId . '?error=' . urlencode($e->getMessage()));
        }

        $quizData = $this->quizService->getQuizForLearner($quizId, $user->id);

        if ($quizData === null) {
            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ]);
        }

        $roles = $this->authService->getUserRoles($user->id);
        $lessonNav = $this->lessonNavigationService->buildForLearner(
            $quizData['course']->id,
            $user->id,
            $quizData['module']->id,
            null,
            $quizId,
        );

        return Response::view('courses/quiz', array_merge([
            'title' => $quizData['quiz']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'quiz' => $quizData['quiz'],
            'module' => $quizData['module'],
            'course' => $quizData['course'],
            'questions' => $quizData['questions'],
            'ticket' => $quizData['ticket'],
            'latestAttempt' => $quizData['latest_attempt'] ?? null,
            'retake' => false,
            'retakeQuizUrl' => url('/quizzes/' . $quizId . '?retake=1'),
            'result' => $result,
            'error' => null,
            'lessonNav' => $lessonNav,
            'retakeLessonUrl' => $this->resolveModuleVideoUrl($quizData['module']->id),
        ], $this->discussionService->buildViewContext(
            $request,
            $quizData['module']->id,
            $user->id,
            '/quizzes/' . $quizId,
        )));
    }

    public function apiShow(Request $request, int $quizId): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $quizData = $this->quizService->getQuizForLearner($quizId, $userId);

        if ($quizData === null) {
            return Response::apiError('FORBIDDEN', __('errors.forbidden'), 403);
        }

        return Response::apiSuccess([
            'quiz' => $quizData['quiz']->toArray(),
            'questions' => array_map(
                static fn ($question) => $question->toLearnerArray(),
                $quizData['questions']
            ),
            'readiness_ticket' => $quizData['ticket']?->toArray(),
        ]);
    }

    public function apiSubmitAttempt(Request $request, int $quizId): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        try {
            $result = $this->quizService->submitAttempt($quizId, $userId, $request->json());
        } catch (ValidationException $e) {
            return Response::apiError('VALIDATION_FAILED', $e->getMessage(), 422, $e->errors());
        } catch (Exception $e) {
            return Response::apiError('FORBIDDEN', $e->getMessage(), 403);
        }

        return Response::apiSuccess($result, 201);
    }
}
