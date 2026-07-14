<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use Exception;

class CourseService
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'archived'];

    public function __construct(
        private readonly CourseRepository $courseRepo,
        private readonly ModuleRepository $moduleRepo,
    ) {
    }

    /** @return array<int, Course> */
    public function listEnrolledCourses(int $userId): array
    {
        return $this->courseRepo->listEnrolledForUser($userId);
    }

    /** @return array<int, Course> */
    public function listManageableCourses(): array
    {
        return $this->courseRepo->listAll();
    }

    /**
     * @return array{course: Course, modules: array<int, Module>}|null
     */
    public function getCourseOutline(int $courseId, int $userId): ?array
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null || $course->status !== 'published') {
            return null;
        }

        if (!$this->courseRepo->isUserEnrolled($userId, $courseId)) {
            return null;
        }

        return [
            'course' => $course,
            'modules' => $this->moduleRepo->listByCourseId($courseId),
        ];
    }

    /**
     * @return array{course: Course, modules: array<int, Module>}|null
     */
    public function getCourseForInstructor(int $courseId): ?array
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            return null;
        }

        return [
            'course' => $course,
            'modules' => $this->moduleRepo->listByCourseId($courseId),
        ];
    }

    /** @param array<string, mixed> $data */
    public function createCourse(array $data): Course
    {
        $validated = $this->validateCourseData($data);

        return $this->courseRepo->create($validated);
    }

    /** @param array<string, mixed> $data */
    public function updateCourse(int $courseId, array $data): Course
    {
        $existing = $this->courseRepo->findById($courseId);

        if ($existing === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateCourseData($data);

        return $this->courseRepo->update($courseId, $validated);
    }

    /** @param array<string, mixed> $data */
    public function createModule(int $courseId, array $data): Module
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateModuleData($data);
        $validated['course_id'] = $courseId;
        $validated['sequence_order'] = $this->moduleRepo->nextSequenceOrder($courseId);

        return $this->moduleRepo->create($validated);
    }

    /** @param array<string, mixed> $data */
    public function updateModule(int $moduleId, array $data): Module
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null) {
            throw new Exception(__('courses.validation.module_not_found'));
        }

        $validated = $this->validateModuleData($data);
        $validated['sequence_order'] = $module->sequenceOrder;

        return $this->moduleRepo->update($moduleId, $validated);
    }

    public function deleteModule(int $moduleId): void
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null) {
            throw new Exception(__('courses.validation.module_not_found'));
        }

        $this->moduleRepo->delete($moduleId);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{title: string, description: ?string, status: string}
     */
    private function validateCourseData(array $data): array
    {
        $errors = [];
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            $errors['title'][] = __('courses.validation.title_required');
        } elseif (mb_strlen($title) > 255) {
            $errors['title'][] = __('courses.validation.title_max');
        }

        $status = (string) ($data['status'] ?? 'draft');

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $errors['status'][] = __('courses.validation.status_invalid');
        }

        $description = trim((string) ($data['description'] ?? ''));

        if ($description === '') {
            $description = null;
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'title' => $title,
            'description' => $description,
            'status' => $status,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{title: string, sequence_order: int}
     */
    private function validateModuleData(array $data): array
    {
        $errors = [];
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            $errors['title'][] = __('courses.validation.module_title_required');
        } elseif (mb_strlen($title) > 255) {
            $errors['title'][] = __('courses.validation.title_max');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'title' => $title,
            'sequence_order' => 1,
        ];
    }
}
