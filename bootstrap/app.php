<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Core\Container;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

$container = new Container();

$container->singleton(Database::class, new Database());

$router = new Router($container);

require base_path('bootstrap/routes.php');

return [
    'container' => $container,
    'router' => $router,
];
