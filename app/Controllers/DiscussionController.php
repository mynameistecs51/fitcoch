<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Url;
use App\Services\AuthService;
use App\Services\DiscussionService;
use App\Services\ValidationException;
use Exception;

class DiscussionController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly DiscussionService $discussionService,
    ) {
    }

    public function apiList(Request $request, int $moduleId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::apiError('UNAUTHORIZED', __('errors.unauthorized'), 401);
        }

        $panel = $this->discussionService->getModulePanel($moduleId, $user->id);

        return Response::apiSuccess([
            'posts' => array_map(
                static fn ($post) => $post->toArray(),
                $panel['posts'],
            ),
            'can_post' => $panel['can_post'],
        ]);
    }

    public function store(Request $request, int $moduleId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return $this->wantsJson($request)
                ? Response::apiError('UNAUTHORIZED', __('errors.unauthorized'), 401)
                : Response::redirect('/login');
        }

        if (!verify_csrf_token($request->input('csrf_token'))) {
            return $this->wantsJson($request)
                ? Response::apiError('CSRF_INVALID', __('errors.invalid_csrf'), 419)
                : $this->redirectBack($request, $moduleId, 'csrf');
        }

        try {
            $post = $this->discussionService->createPost($moduleId, $user->id, $request->all());
        } catch (ValidationException $e) {
            return $this->wantsJson($request)
                ? Response::apiError('VALIDATION_FAILED', $e->getMessage(), 422, $e->errors())
                : $this->redirectBack($request, $moduleId, 'validation');
        } catch (Exception $e) {
            return $this->wantsJson($request)
                ? Response::apiError('FORBIDDEN', $e->getMessage(), 403)
                : $this->redirectBack($request, $moduleId, urlencode($e->getMessage()));
        }

        if ($this->wantsJson($request)) {
            return Response::apiSuccess(['post' => $post->toArray()]);
        }

        return $this->redirectBack($request, $moduleId, null, 'posted');
    }

    private function wantsJson(Request $request): bool
    {
        if ($request->isApi()) {
            return true;
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return true;
        }

        $accept = $request->header('Accept', '');

        return str_contains($accept, 'application/json');
    }

    private function redirectBack(Request $request, int $moduleId, ?string $error = null, ?string $success = null): Response
    {
        $redirect = $this->normalizeRedirectPath((string) $request->input('redirect', ''));

        $query = [];

        if ($success !== null) {
            $query['discussion_success'] = $success;
        }

        if ($error !== null) {
            $query['discussion_error'] = $error;
        }

        $query['discussion_module'] = (string) $moduleId;

        $separator = str_contains($redirect, '?') ? '&' : '?';

        return Response::redirect($redirect . $separator . http_build_query($query) . '#discussion-board');
    }

    private function normalizeRedirectPath(string $redirect): string
    {
        $redirect = trim($redirect);

        if ($redirect === '') {
            return '/courses';
        }

        $base = Url::base();

        if ($base !== '' && str_starts_with($redirect, $base)) {
            $redirect = substr($redirect, strlen($base)) ?: '/';
        }

        if (!str_starts_with($redirect, '/')) {
            return '/courses';
        }

        return $redirect;
    }
}
