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
                    'You do not have permission to access this resource.',
                    403
                );
            }

            return Response::view('errors/forbidden', [
                'title' => 'Access Denied',
            ], 403);
        }

        return $next($request);
    }
}
