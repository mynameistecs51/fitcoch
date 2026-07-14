<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\NuggetService;
use App\Services\ValidationException;
use Exception;

class NuggetController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly NuggetService $nuggetService,
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

        return Response::view('courses/nugget', [
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
        ]);
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
