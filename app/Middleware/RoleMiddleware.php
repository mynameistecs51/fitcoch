<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthorizationService;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthorizationService $authzService)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        /** @var array<int, string> $requiredRoles */
        $requiredRoles = $request->getAttribute('required_roles', []);

        if ($requiredRoles === []) {
            return $next($request);
        }

        $userId = (int) $request->getAttribute('user_id', 0);

        if ($userId === 0 || !$this->authzService->hasAnyRole($userId, $requiredRoles)) {
            if ($request->isApi()) {
                return Response::apiError(
                    'FORBIDDEN',
                    __('errors.forbidden'),
                    403
                );
            }

            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ], 403);
        }

        return $next($request);
    }
}
