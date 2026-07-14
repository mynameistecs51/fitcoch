<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CourseController;
use App\Controllers\InstructorCourseController;
use App\Controllers\LocaleController;
use App\Controllers\NuggetController;
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

// Course routes (learner)
$router->get('/courses', [CourseController::class, 'index'], $authMiddleware);
$router->get('/courses/{courseId}', [CourseController::class, 'show'], $authMiddleware);

// Nugget lesson routes (learner)
$router->get('/nuggets/{nuggetId}', [NuggetController::class, 'show'], $authMiddleware);
$router->get('/nuggets/{nuggetId}/stream', [NuggetController::class, 'stream'], $authMiddleware);

// Instructor course management
$instructorRoles = ['instructor', 'admin'];
$router->get('/instructor/courses', [InstructorCourseController::class, 'index'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/create', [InstructorCourseController::class, 'create'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses', [InstructorCourseController::class, 'store'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/edit', [InstructorCourseController::class, 'edit'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}', [InstructorCourseController::class, 'update'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules', [InstructorCourseController::class, 'storeModule'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/delete', [InstructorCourseController::class, 'deleteModule'], $authRoleMiddleware, $instructorRoles);

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

// API routes — Courses
$router->get('/api/v1/courses', [CourseController::class, 'apiList'], $authMiddleware);
$router->get('/api/v1/courses/{courseId}', [CourseController::class, 'apiShow'], $authMiddleware);

// API routes — Nuggets
$router->get('/api/v1/nuggets/{nuggetId}', [NuggetController::class, 'apiShow'], $authMiddleware);
$router->post('/api/v1/nuggets/{nuggetId}/progress', [NuggetController::class, 'apiProgress'], $authMiddleware);

// API routes — RBAC demo (instructor/admin only)
$router->get('/api/v1/instructor/ping', [UserController::class, 'instructorPing'], $authRoleMiddleware, ['instructor', 'admin']);

// API routes — Admin
$router->get('/api/v1/admin/users', [AdminController::class, 'apiListUsers'], $authRoleMiddleware, $adminRoles);
