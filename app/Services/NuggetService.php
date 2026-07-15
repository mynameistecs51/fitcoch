<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\NuggetRepository;
use Exception;

class NuggetService
{
    public function __construct(
        private readonly NuggetRepository $nuggetRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly CourseRepository $courseRepo,
        private readonly NuggetProgressRepository $progressRepo,
        private readonly VideoService $videoService,
        private readonly LessonUnlockService $unlockService,
    ) {
    }

    /**
     * @return array{
     *     nugget: Nugget,
     *     module: Module,
     *     course: Course,
     *     progress: ?array<string, mixed>,
     *     youtube_id: ?string,
     *     stream_url: ?string
     * }|null
     */
    public function getLessonForLearner(int $nuggetId, int $userId): ?array
    {
        $nugget = $this->nuggetRepo->findById($nuggetId);

        if ($nugget === null || $nugget->nuggetType !== 'video') {
            return null;
        }

        $module = $this->moduleRepo->findById($nugget->moduleId);

        if ($module === null) {
            return null;
        }

        $course = $this->courseRepo->findById($module->courseId);

        if ($course === null || $course->status !== 'published') {
            return null;
        }

        if (!$this->courseRepo->isUserEnrolled($userId, $course->id)) {
            return null;
        }

        if (!$this->unlockService->canAccessNugget($nuggetId, $userId)) {
            return null;
        }

        $progress = $this->progressRepo->find($userId, $nuggetId);
        $youtubeId = $nugget->contentUrl ? $this->videoService->extractYoutubeId((string) $nugget->contentUrl) : null;
        $streamUrl = $youtubeId === null && $nugget->contentUrl
            ? url('/nuggets/' . $nugget->id . '/stream')
            : null;

        return [
            'nugget' => $nugget,
            'module' => $module,
            'course' => $course,
            'progress' => $progress,
            'youtube_id' => $youtubeId,
            'stream_url' => $streamUrl,
        ];
    }

    public function canStreamNugget(int $nuggetId, int $userId): bool
    {
        return $this->getLessonForLearner($nuggetId, $userId) !== null;
    }

    public function streamNuggetVideo(int $nuggetId, int $userId): void
    {
        $lesson = $this->getLessonForLearner($nuggetId, $userId);

        if ($lesson === null) {
            http_response_code(403);
            exit;
        }

        $filePath = $this->videoService->resolveUploadedVideoPath((string) ($lesson['nugget']->contentUrl ?? ''));

        if ($filePath === null) {
            http_response_code(404);
            exit;
        }

        $this->videoService->streamFile($filePath);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateProgress(int $nuggetId, int $userId, array $data): array
    {
        if ($this->getLessonForLearner($nuggetId, $userId) === null) {
            throw new Exception(__('errors.forbidden'));
        }

        $percentage = (int) ($data['progress_percentage'] ?? 0);

        if ($percentage < 0 || $percentage > 100) {
            throw new ValidationException(__('errors.validation_failed'), [
                'progress_percentage' => [__('nuggets.validation.progress_invalid')],
            ]);
        }

        $record = $this->progressRepo->upsert($userId, $nuggetId, $percentage);

        return [
            'nugget_id' => $nuggetId,
            'progress_percentage' => (int) $record['progress_percentage'],
            'status' => (string) $record['status'],
            'completed_at' => $record['completed_at'],
        ];
    }
}
