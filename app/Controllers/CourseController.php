<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\CourseService;

class CourseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CourseService $courseService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);

        return Response::view('courses/index', [
            'title' => __('courses.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'courses' => $this->courseService->listEnrolledCourses($user->id),
        ]);
    }

    public function show(Request $request, int $courseId): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $outline = $this->courseService->getCourseOutline($courseId, $user->id);

        if ($outline === null) {
            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ]);
        }

        $roles = $this->authService->getUserRoles($user->id);

        return Response::view('courses/show', [
            'title' => $outline['course']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $outline['course'],
            'modules' => $outline['modules'],
        ]);
    }

    public function apiList(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $courses = $this->courseService->listEnrolledCourses($userId);

        $data = array_map(static function ($course): array {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status,
                'progress_percentage' => 0,
            ];
        }, $courses);

        return Response::apiSuccess($data);
    }

    public function apiShow(Request $request, int $courseId): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $outline = $this->courseService->getCourseOutline($courseId, $userId);

        if ($outline === null) {
            return Response::apiError('FORBIDDEN', __('errors.forbidden'), 403);
        }

        $modules = array_map(static function ($module): array {
            return array_merge($module->toArray(), [
                'nuggets' => [],
                'status' => 'unlocked',
            ]);
        }, $outline['modules']);

        return Response::apiSuccess([
            'course' => $outline['course']->toArray(),
            'modules' => $modules,
        ]);
    }
}
