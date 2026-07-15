<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\LiveSessionService;

class ReadinessGateMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LiveSessionService $liveSessionService,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $sessionId = (int) $request->getAttribute('id', 0);

        if ($sessionId === 0) {
            return $next($request);
        }

        $user = $this->authService->currentUser();

        if ($user === null) {
            if ($request->isApi()) {
                return Response::apiError('UNAUTHORIZED', __('errors.unauthorized'), 401);
            }

            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);
        $context = $this->liveSessionService->getRoomContext($sessionId, $user->id, $roles);

        if ($context === null) {
            if ($request->isApi()) {
                return Response::apiError('NOT_FOUND', __('live.validation.not_found'), 404);
            }

            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ], 403);
        }

        if (!$context['can_join']) {
            if ($request->isApi()) {
                return Response::apiError('READINESS_GATE_BLOCKED', __('live.validation.gate_blocked'), 403);
            }

            return Response::view('live/gate-blocked', [
                'title' => __('live.gate_title'),
                'user' => $user,
                'roles' => $roles,
                'isAdmin' => in_array('admin', $roles, true),
                'session' => $context['session'],
                'module' => $context['module'],
                'course' => $context['course'],
                'ticket' => $context['ticket'],
            ], 403);
        }

        $request->setAttribute('live_context', $context);

        return $next($request);
    }
}
