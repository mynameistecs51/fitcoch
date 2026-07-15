<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CourseController;
use App\Controllers\InstructorCohortController;
use App\Controllers\InstructorKnowledgeItemController;
use App\Controllers\InstructorCourseController;
use App\Controllers\InstructorCourseProgressController;
use App\Controllers\InstructorLiveSessionController;
use App\Controllers\InstructorQuizController;
use App\Controllers\InstructorReadinessController;
use App\Controllers\LiveSessionController;
use App\Controllers\LocaleController;
use App\Controllers\NuggetController;
use App\Controllers\QuizController;
use App\Controllers\ReviewController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ReadinessGateMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Core\Router $router */

$authMiddleware = [AuthMiddleware::class];
$authRoleMiddleware = [AuthMiddleware::class, RoleMiddleware::class];
$liveGateMiddleware = [AuthMiddleware::class, ReadinessGateMiddleware::class];

// Language switcher
$router->get('/lang/{locale}', [LocaleController::class, 'switch']);

// Web routes
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'sendForgotPassword']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout'], $authMiddleware);
$router->get('/dashboard', [AuthController::class, 'dashboard'], $authMiddleware);
$router->get('/profile', [UserController::class, 'showProfile'], $authMiddleware);
$router->post('/profile', [UserController::class, 'updateProfile'], $authMiddleware);

// Course routes (learner)
$router->get('/courses', [CourseController::class, 'index'], $authMiddleware);
$router->post('/courses/{courseId}/enroll', [CourseController::class, 'enroll'], $authMiddleware);
$router->get('/courses/{courseId}', [CourseController::class, 'show'], $authMiddleware);

// Nugget lesson routes (learner)
$router->get('/nuggets/{nuggetId}', [NuggetController::class, 'show'], $authMiddleware);
$router->get('/nuggets/{nuggetId}/stream', [NuggetController::class, 'stream'], $authMiddleware);

// Quiz routes (learner)
$router->get('/quizzes/{quizId}', [QuizController::class, 'show'], $authMiddleware);
$router->post('/quizzes/{quizId}/attempts', [QuizController::class, 'submit'], $authMiddleware);

// Spaced repetition review routes (learner)
$router->get('/review/daily', [ReviewController::class, 'showDaily'], $authMiddleware);
$router->post('/review/daily/{knowledgeItemId}/respond', [ReviewController::class, 'respond'], $authMiddleware);

// Live classroom routes (learner)
$router->get('/live/{id}', [LiveSessionController::class, 'show'], $liveGateMiddleware);
$router->post('/api/v1/live/{id}/join', [LiveSessionController::class, 'apiJoin'], $liveGateMiddleware);
$router->post('/api/v1/live/{id}/leave', [LiveSessionController::class, 'apiLeave'], $authMiddleware);
$router->get('/api/v1/live/{id}/participants', [LiveSessionController::class, 'apiParticipants'], $authMiddleware);
$router->post('/api/v1/live/{id}/activate', [LiveSessionController::class, 'apiActivate'], $authMiddleware);
$router->post('/api/v1/live/{id}/complete', [LiveSessionController::class, 'apiComplete'], $authMiddleware);

// Instructor course management
$instructorRoles = ['instructor', 'admin'];
$router->get('/instructor/courses', [InstructorCourseController::class, 'index'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/create', [InstructorCourseController::class, 'create'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses', [InstructorCourseController::class, 'store'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/progress', [InstructorCourseProgressController::class, 'show'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/cohorts', [InstructorCohortController::class, 'index'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/cohorts', [InstructorCohortController::class, 'store'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/cohorts/{cohortId}', [InstructorCohortController::class, 'update'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/cohorts/{cohortId}/enroll', [InstructorCohortController::class, 'enroll'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/cohorts/{cohortId}/enrollments/{learnerId}/drop', [InstructorCohortController::class, 'drop'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/knowledge-items', [InstructorKnowledgeItemController::class, 'index'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/knowledge-items', [InstructorKnowledgeItemController::class, 'store'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/knowledge-items/sync', [InstructorKnowledgeItemController::class, 'sync'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/knowledge-items/{itemId}', [InstructorKnowledgeItemController::class, 'update'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/knowledge-items/{itemId}/delete', [InstructorKnowledgeItemController::class, 'delete'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/edit', [InstructorCourseController::class, 'edit'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}', [InstructorCourseController::class, 'update'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules', [InstructorCourseController::class, 'storeModule'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}', [InstructorCourseController::class, 'updateModule'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/delete', [InstructorCourseController::class, 'deleteModule'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/modules/{moduleId}/readiness', [InstructorReadinessController::class, 'show'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/readiness/{learnerId}/override', [InstructorReadinessController::class, 'override'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/readiness/{learnerId}/lock', [InstructorReadinessController::class, 'lock'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/modules/{moduleId}/quiz/import/template', [InstructorQuizController::class, 'downloadImportTemplate'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/quiz/{quizId}/import', [InstructorQuizController::class, 'importQuestions'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/modules/{moduleId}/quiz', [InstructorQuizController::class, 'edit'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/quiz', [InstructorQuizController::class, 'saveQuiz'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/quiz/{quizId}/delete', [InstructorQuizController::class, 'deleteQuiz'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/quiz/{quizId}/questions', [InstructorQuizController::class, 'saveQuestion'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/quiz/{quizId}/questions/{questionId}/delete', [InstructorQuizController::class, 'deleteQuestion'], $authRoleMiddleware, $instructorRoles);
$router->get('/instructor/courses/{courseId}/modules/{moduleId}/live-sessions', [InstructorLiveSessionController::class, 'index'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/live-sessions', [InstructorLiveSessionController::class, 'store'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/live-sessions/{sessionId}/activate', [InstructorLiveSessionController::class, 'activate'], $authRoleMiddleware, $instructorRoles);
$router->post('/instructor/courses/{courseId}/modules/{moduleId}/live-sessions/{sessionId}/complete', [InstructorLiveSessionController::class, 'complete'], $authRoleMiddleware, $instructorRoles);

// Admin routes (admin only)
$adminRoles = ['admin'];
$router->get('/admin/users', [AdminController::class, 'index'], $authRoleMiddleware, $adminRoles);
$router->get('/admin/users/import/template', [AdminController::class, 'downloadImportTemplate'], $authRoleMiddleware, $adminRoles);
$router->post('/admin/users/import', [AdminController::class, 'importUsers'], $authRoleMiddleware, $adminRoles);
$router->get('/admin/users/{id}', [AdminController::class, 'edit'], $authRoleMiddleware, $adminRoles);
$router->post('/admin/users/{id}', [AdminController::class, 'updateAccount'], $authRoleMiddleware, $adminRoles);
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

// API routes — Quizzes
$router->get('/api/v1/quizzes/{quizId}', [QuizController::class, 'apiShow'], $authMiddleware);
$router->post('/api/v1/quizzes/{quizId}/attempts', [QuizController::class, 'apiSubmitAttempt'], $authMiddleware);

// API routes — Spaced Repetition Reviews
$router->get('/api/v1/reviews/daily', [ReviewController::class, 'apiDaily'], $authMiddleware);
$router->post('/api/v1/reviews/{knowledgeItemId}/respond', [ReviewController::class, 'respond'], $authMiddleware);

// API routes — RBAC demo (instructor/admin only)
$router->get('/api/v1/instructor/ping', [UserController::class, 'instructorPing'], $authRoleMiddleware, ['instructor', 'admin']);

// API routes — Admin
$router->get('/api/v1/admin/users', [AdminController::class, 'apiListUsers'], $authRoleMiddleware, $adminRoles);
