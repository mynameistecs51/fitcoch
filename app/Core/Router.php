<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use Exception;

class Router
{
    /** @var array<int, array{method: string, path: string, pattern: string, handler: callable|array, middleware: array<int, string>, roles: array<int, string>}> */
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, callable|array $handler, array $middleware = [], array $roles = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware, $roles);
    }

    public function post(string $path, callable|array $handler, array $middleware = [], array $roles = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware, $roles);
    }

    private function addRoute(
        string $method,
        string $path,
        callable|array $handler,
        array $middleware,
        array $roles = [],
    ): void {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
            'roles' => $roles,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (!preg_match($route['pattern'], $request->uri(), $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn ($key) => !is_int($key),
                ARRAY_FILTER_USE_KEY
            );

            if ($route['roles'] !== []) {
                $request->setAttribute('required_roles', $route['roles']);
            }

            $handler = $this->resolveHandler($route['handler'], $params);

            $pipeline = $this->buildPipeline($route['middleware'], $handler);

            return $pipeline($request);
        }

        if ($request->isApi()) {
            return Response::apiError('NOT_FOUND', __('errors.page_not_found'), 404);
        }

        return Response::apiError('NOT_FOUND', __('errors.not_found'), 404);
    }

    /** @param array<string, string> $params */
    private function resolveHandler(callable|array $handler, array $params): callable
    {
        if (is_callable($handler)) {
            return static fn (Request $request) => $handler($request, ...array_values($params));
        }

        [$class, $method] = $handler;
        $controller = $this->container->get($class);

        return function (Request $request) use ($controller, $method, $params) {
            return $controller->{$method}($request, ...array_values($params));
        };
    }

    /** @param array<int, string> $middleware */
    private function buildPipeline(array $middleware, callable $handler): callable
    {
        $pipeline = $handler;

        foreach (array_reverse($middleware) as $middlewareClass) {
            $pipeline = function (Request $request) use ($middlewareClass, $pipeline) {
                /** @var MiddlewareInterface $middleware */
                $middleware = $this->container->get($middlewareClass);

                return $middleware->handle($request, $pipeline);
            };
        }

        return $pipeline;
    }
}
