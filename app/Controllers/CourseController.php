<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CohortRepository;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\DiscussionService;
use App\Services\LessonNavigationService;
use App\Services\QuizService;

class CourseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CourseService $courseService,
        private readonly QuizService $quizService,
        private readonly CohortRepository $cohortRepo,
        private readonly LessonNavigationService $lessonNavigationService,
        private readonly DiscussionService $discussionService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);
        $courses = $this->courseService->listEnrolledCourses($user->id);
        $courseIds = array_map(static fn ($course) => $course->id, $courses);
        $isInstructor = in_array('instructor', $roles, true) || in_array('admin', $roles, true);
        $unreadCounts = $isInstructor
            ? $this->discussionService->countUnreadByCourseIds($user->id, $courseIds)
            : [];
        $courseOutlines = [];

        foreach ($courses as $course) {
            $summary = $this->lessonNavigationService->buildSyllabusSummary($course->id, $user->id);

            if ($summary !== null) {
                $courseOutlines[$course->id] = $summary;
            }
        }

        return Response::view('courses/index', [
            'title' => __('courses.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'isInstructor' => $isInstructor,
            'courses' => $courses,
            'courseOutlines' => $courseOutlines,
            'unreadCounts' => $unreadCounts,
            'availableCourses' => $this->courseService->listAvailableCourses($user->id),
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function enroll(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/courses?error=csrf');
        }

        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $this->courseService->enrollLearner($user->id, $courseId);
        } catch (\Exception $e) {
            return Response::redirect('/courses?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/courses?success=enrolled');
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

        $resumeLessonUrl = $this->lessonNavigationService->findResumeLessonUrl($courseId, $user->id);

        if ($resumeLessonUrl !== null && ($request->query()['view'] ?? '') !== 'syllabus') {
            return Response::redirect($resumeLessonUrl);
        }

        $roles = $this->authService->getUserRoles($user->id);
        $moduleIds = array_map(static fn ($module) => $module->id, $outline['modules']);
        $quizzesByModule = $this->quizService->listQuizzesByModuleIds($moduleIds);
        $cohort = $this->cohortRepo->findActiveEnrollmentForUser($user->id, $courseId);
        $ticketsByModule = $cohort !== null
            ? $this->quizService->listTicketsForModules($user->id, $cohort->id, $moduleIds)
            : [];
        $lessonNav = $outline['modules'] !== []
            ? $this->lessonNavigationService->buildForLearner(
                $courseId,
                $user->id,
                $outline['modules'][0]->id,
            )
            : null;
        $syllabusSummary = $this->lessonNavigationService->buildSyllabusSummary($courseId, $user->id);

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
            'lessonNav' => $lessonNav,
            'resumeLessonUrl' => $resumeLessonUrl,
            'syllabusSummary' => $syllabusSummary,
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
