<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\InstructorCourseProgressService;

class InstructorCourseProgressController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly InstructorCourseProgressService $progressService,
    ) {
    }

    public function show(Request $request, int $courseId): Response
    {
        $report = $this->progressService->buildCourseReport($courseId);

        if ($report === null) {
            return Response::redirect('/instructor/courses?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/courses/progress', [
            'title' => __('courses.instructor.progress_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'report' => $report,
        ]);
    }
}
