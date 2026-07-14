<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $this->refreshSessionTimeout();

        $userId = $_SESSION['user_id'] ?? null;

        if ($userId === null) {
            $token = $request->bearerToken();

            if ($token !== null) {
                $user = $this->authService->userFromToken($token);

                if ($user !== null) {
                    $request->setAttribute('user_id', $user->id);
                    $request->setAttribute('user', $user);

                    return $next($request);
                }
            }

            if ($request->isApi()) {
                return Response::apiError(
                    'UNAUTHORIZED',
                    __('errors.unauthorized'),
                    401
                );
            }

            return Response::redirect('/login');
        }

        $request->setAttribute('user_id', (int) $userId);

        return $next($request);
    }

    private function refreshSessionTimeout(): void
    {
        $lifetime = (int) config('app.session_lifetime', 1800);

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $lifetime) {
            $this->authService->logout();
        }

        if (isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();
        }
    }
}
