<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CohortRepository;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\LiveSessionService;
use App\Services\QuizService;

class CourseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CourseService $courseService,
        private readonly QuizService $quizService,
        private readonly LiveSessionService $liveSessionService,
        private readonly CohortRepository $cohortRepo,
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
        $moduleIds = array_map(static fn ($module) => $module->id, $outline['modules']);
        $quizzesByModule = $this->quizService->listQuizzesByModuleIds($moduleIds);
        $cohort = $this->cohortRepo->findActiveEnrollmentForUser($user->id, $courseId);
        $ticketsByModule = $cohort !== null
            ? $this->quizService->listTicketsForModules($user->id, $cohort->id, $moduleIds)
            : [];
        $sessionsByModule = $this->liveSessionService->listSessionsByModuleIds($moduleIds);

        return Response::view('courses/show', [
            'title' => $outline['course']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $outline['course'],
            'modules' => $outline['modules'],
            'nuggetsByModule' => $outline['nuggetsByModule'],
            'quizzesByModule' => $quizzesByModule,
            'ticketsByModule' => $ticketsByModule,
            'sessionsByModule' => $sessionsByModule,
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

        $modules = array_map(function ($module) use ($outline): array {
            $nuggets = $outline['nuggetsByModule'][$module->id] ?? [];

            return array_merge($module->toArray(), [
                'nuggets' => array_map(static fn ($nugget) => $nugget->toArray(), $nuggets),
                'status' => 'unlocked',
            ]);
        }, $outline['modules']);

        return Response::apiSuccess([
            'course' => $outline['course']->toArray(),
            'modules' => $modules,
        ]);
    }
}
