<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\SpacedRepetitionService;
use App\Services\ValidationException;
use Exception;

class ReviewController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SpacedRepetitionService $reviewService,
    ) {
    }

    public function showDaily(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);
        $panel = $this->reviewService->getDailyPanel($user);

        return Response::view('reviews/daily', [
            'title' => __('reviews.daily_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'panel' => $panel,
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function respond(Request $request, int $knowledgeItemId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$request->isApi() && !verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/review/daily?error=csrf');
        }

        $rating = (int) ($request->isApi()
            ? ($request->json()['rating'] ?? $request->input('rating'))
            : $request->input('rating'));

        try {
            $result = $this->reviewService->submitRating($user, $knowledgeItemId, $rating);
        } catch (ValidationException $e) {
            if ($request->isApi()) {
                return Response::apiError('VALIDATION_FAILED', $e->getMessage(), 422, $e->errors());
            }

            return Response::redirect('/review/daily?error=validation');
        } catch (Exception $e) {
            if ($request->isApi()) {
                return Response::apiError('REVIEW_FAILED', $e->getMessage(), 400);
            }

            return Response::redirect('/review/daily?error=' . urlencode($e->getMessage()));
        }

        if ($request->isApi()) {
            return Response::apiSuccess([
                'next_review_date' => $result['next_review_date'],
                'interval_days' => $result['interval_days'],
                'easiness_factor' => $result['easiness_factor'],
                'repetition_number' => $result['repetition_number'],
            ]);
        }

        return Response::redirect('/review/daily?success=rated');
    }

    public function apiDaily(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::apiError('UNAUTHORIZED', __('errors.unauthorized'), 401);
        }

        $items = $this->reviewService->listDailyQueue($user);

        return Response::apiSuccess([
            'items' => $items,
            'due_count' => count($items),
        ]);
    }
}
