<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\QuizService;
use Exception;

class InstructorReadinessController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly QuizService $quizService,
    ) {
    }

    public function show(Request $request, int $courseId, int $moduleId): Response
    {
        $panel = $this->quizService->getInstructorReadinessPanel($courseId, $moduleId);

        if ($panel === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/readiness/index', [
            'title' => __('quizzes.instructor.readiness_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'cohort' => $panel['cohort'],
            'module' => $panel['module'],
            'quiz' => $panel['quiz'],
            'tickets' => $panel['tickets'],
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function override(Request $request, int $courseId, int $moduleId, int $learnerId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?error=csrf');
        }

        $user = $this->authService->currentUser();

        try {
            $this->quizService->overrideTicket($courseId, $moduleId, $learnerId, $user?->id ?? 0);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?success=overridden');
    }

    public function lock(Request $request, int $courseId, int $moduleId, int $learnerId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?error=csrf');
        }

        try {
            $this->quizService->lockTicket($courseId, $moduleId, $learnerId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/readiness?success=locked');
    }
}
