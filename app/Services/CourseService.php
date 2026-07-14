<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\NuggetRepository;
use Exception;

class CourseService
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'archived'];

    private const VIDEO_SOURCES = ['none', 'youtube', 'upload'];

    public function __construct(
        private readonly CourseRepository $courseRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly NuggetRepository $nuggetRepo,
        private readonly VideoService $videoService,
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
     * @return array{course: Course, modules: array<int, Module>, nuggetsByModule: array<int, array<int, Nugget>>}|null
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

        return $this->buildCourseOutline($course);
    }

    /**
     * @return array{course: Course, modules: array<int, Module>, nuggetsByModule: array<int, array<int, Nugget>>}|null
     */
    public function getCourseForInstructor(int $courseId): ?array
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            return null;
        }

        return $this->buildCourseOutline($course);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    public function createCourse(array $data, array $files = []): Course
    {
        $validated = $this->validateCourseData($data);
        $course = $this->courseRepo->create($validated);
        $this->maybeAttachVideoNugget($course, $data, $files);

        return $course;
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

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    public function createModule(int $courseId, array $data, array $files = []): Module
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateModuleData($data);
        $validated['course_id'] = $courseId;
        $validated['sequence_order'] = $this->moduleRepo->nextSequenceOrder($courseId);

        $module = $this->moduleRepo->create($validated);
        $this->maybeAttachVideoNuggetToModule($module, $data, $files);

        return $module;
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
     * @return array{course: Course, modules: array<int, Module>, nuggetsByModule: array<int, array<int, Nugget>>}
     */
    private function buildCourseOutline(Course $course): array
    {
        $modules = $this->moduleRepo->listByCourseId($course->id);
        $nuggetsByModule = [];

        foreach ($modules as $module) {
            $nuggetsByModule[$module->id] = $this->nuggetRepo->listByModuleId($module->id);
        }

        return [
            'course' => $course,
            'modules' => $modules,
            'nuggetsByModule' => $nuggetsByModule,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    private function maybeAttachVideoNugget(Course $course, array $data, array $files): void
    {
        $source = $this->normalizeVideoSource($data);

        if ($source === 'none') {
            return;
        }

        $moduleTitle = trim((string) ($data['module_title'] ?? ''));

        if ($moduleTitle === '') {
            $moduleTitle = __('courses.form.default_module_title');
        }

        $module = $this->moduleRepo->create([
            'course_id' => $course->id,
            'title' => $moduleTitle,
            'sequence_order' => 1,
        ]);

        $this->maybeAttachVideoNuggetToModule($module, $data, $files);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     */
    private function maybeAttachVideoNuggetToModule(Module $module, array $data, array $files): void
    {
        $source = $this->normalizeVideoSource($data);

        if ($source === 'none') {
            return;
        }

        $nuggetPayload = $this->resolveVideoNuggetPayload($data, $files, $source);

        $this->nuggetRepo->create([
            'module_id' => $module->id,
            'title' => $nuggetPayload['title'],
            'nugget_type' => 'video',
            'content_url' => $nuggetPayload['content_url'],
            'content_body' => null,
            'duration_seconds' => $nuggetPayload['duration_seconds'],
            'sequence_order' => $this->nuggetRepo->nextSequenceOrder($module->id),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $files
     * @return array{title: string, content_url: string, duration_seconds: int}
     */
    private function resolveVideoNuggetPayload(array $data, array $files, string $source): array
    {
        $title = trim((string) ($data['nugget_title'] ?? ''));

        if ($title === '') {
            $title = trim((string) ($data['module_title'] ?? ''));

            if ($title === '') {
                $title = trim((string) ($data['title'] ?? ''));
            }

            if ($title === '') {
                $title = __('courses.form.default_nugget_title');
            }
        }

        if ($source === 'youtube') {
            $youtubeUrl = $this->videoService->normalizeYoutubeUrl((string) ($data['youtube_url'] ?? ''));

            if ($youtubeUrl === null) {
                throw new ValidationException(__('errors.validation_failed'), [
                    'youtube_url' => [__('courses.validation.youtube_url_invalid')],
                ]);
            }

            return [
                'title' => $title,
                'content_url' => $youtubeUrl,
                'duration_seconds' => 0,
            ];
        }

        $uploadedFile = $files['video_file'] ?? null;

        if (!is_array($uploadedFile)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'video_file' => [__('courses.validation.video_file_required')],
            ]);
        }

        try {
            $stored = $this->videoService->storeUploadedVideo($uploadedFile);
        } catch (Exception $e) {
            throw new ValidationException(__('errors.validation_failed'), [
                'video_file' => [$e->getMessage()],
            ]);
        }

        return [
            'title' => $title,
            'content_url' => $stored['content_url'],
            'duration_seconds' => $stored['duration_seconds'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function normalizeVideoSource(array $data): string
    {
        $source = (string) ($data['video_source'] ?? 'none');

        return in_array($source, self::VIDEO_SOURCES, true) ? $source : 'none';
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
