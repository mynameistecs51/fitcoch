<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\LocaleController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Core\Router $router */

$authMiddleware = [AuthMiddleware::class];
$authRoleMiddleware = [AuthMiddleware::class, RoleMiddleware::class];

// Language switcher
$router->get('/lang/{locale}', [LocaleController::class, 'switch']);

// Web routes
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout'], $authMiddleware);
$router->get('/dashboard', [AuthController::class, 'dashboard'], $authMiddleware);
$router->get('/profile', [UserController::class, 'showProfile'], $authMiddleware);
$router->post('/profile', [UserController::class, 'updateProfile'], $authMiddleware);

// Admin routes (admin only)
$adminRoles = ['admin'];
$router->get('/admin/users', [AdminController::class, 'index'], $authRoleMiddleware, $adminRoles);
$router->get('/admin/users/{id}', [AdminController::class, 'edit'], $authRoleMiddleware, $adminRoles);
$router->post('/admin/users/{id}/roles', [AdminController::class, 'updateRoles'], $authRoleMiddleware, $adminRoles);
$router->post('/admin/users/{id}/status', [AdminController::class, 'updateStatus'], $authRoleMiddleware, $adminRoles);

// API routes — Authentication
$router->post('/api/v1/auth/login', [AuthController::class, 'login']);
$router->post('/api/v1/auth/register', [AuthController::class, 'register']);
$router->post('/api/v1/auth/logout', [AuthController::class, 'logout'], $authMiddleware);

// API routes — Users
$router->get('/api/v1/users/me', [UserController::class, 'me'], $authMiddleware);

// API routes — RBAC demo (instructor/admin only)
$router->get('/api/v1/instructor/ping', [UserController::class, 'instructorPing'], $authRoleMiddleware, ['instructor', 'admin']);

// API routes — Admin
$router->get('/api/v1/admin/users', [AdminController::class, 'apiListUsers'], $authRoleMiddleware, $adminRoles);
