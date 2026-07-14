<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\CourseService;
use App\Services\ValidationException;
use Exception;

class InstructorCourseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CourseService $courseService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/courses/index', [
            'title' => __('courses.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'courses' => $this->courseService->listManageableCourses(),
            'success' => $request->query()['success'] ?? null,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/courses/form', [
            'title' => __('courses.instructor.create_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => null,
            'modules' => [],
            'form' => [],
            'errors' => [],
            'error' => null,
        ]);
    }

    public function store(Request $request): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return $this->renderCreateWithError(__('errors.invalid_csrf'));
        }

        try {
            $course = $this->courseService->createCourse($request->all());
        } catch (ValidationException $e) {
            return Response::view('instructor/courses/form', $this->formViewData(null, [], $request->all(), $e->errors()));
        }

        return Response::redirect('/instructor/courses/' . $course->id . '/edit?success=created');
    }

    public function edit(Request $request, int $courseId): Response
    {
        $outline = $this->courseService->getCourseForInstructor($courseId);

        if ($outline === null) {
            return Response::redirect('/instructor/courses?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/courses/form', [
            'title' => __('courses.instructor.edit_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $outline['course'],
            'modules' => $outline['modules'],
            'form' => [],
            'errors' => [],
            'error' => $request->query()['error'] ?? null,
            'success' => $request->query()['success'] ?? null,
        ]);
    }

    public function update(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=csrf');
        }

        try {
            $this->courseService->updateCourse($courseId, $request->all());
        } catch (ValidationException $e) {
            $outline = $this->courseService->getCourseForInstructor($courseId);

            return Response::view('instructor/courses/form', $this->formViewData(
                $outline['course'] ?? null,
                $outline['modules'] ?? [],
                $request->all(),
                $e->errors()
            ));
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/edit?success=updated');
    }

    public function storeModule(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=csrf');
        }

        try {
            $this->courseService->createModule($courseId, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=module');
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/edit?success=module_added');
    }

    public function deleteModule(Request $request, int $courseId, int $moduleId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=csrf');
        }

        try {
            $this->courseService->deleteModule($moduleId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/edit?success=module_deleted');
    }

    private function renderCreateWithError(string $error): Response
    {
        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/courses/form', array_merge($this->formViewData(null, [], [], []), [
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'error' => $error,
        ]));
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, array<int, string>> $errors
     * @return array<string, mixed>
     */
    private function formViewData(?\App\Models\Course $course, array $modules, array $form, array $errors): array
    {
        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return [
            'title' => $course ? __('courses.instructor.edit_title') : __('courses.instructor.create_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $course,
            'modules' => $modules,
            'form' => $form,
            'errors' => $errors,
            'error' => null,
        ];
    }
}
