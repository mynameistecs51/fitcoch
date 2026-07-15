<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;

class LessonUnlockService
{
    public function __construct(
        private readonly CourseService $courseService,
        private readonly NuggetProgressRepository $progressRepo,
        private readonly QuizRepository $quizRepo,
        private readonly QuizAttemptRepository $attemptRepo,
    ) {
    }

    public function canAccessNugget(int $nuggetId, int $userId): bool
    {
        $location = $this->findNuggetLocation($nuggetId, $userId);

        if ($location === null) {
            return false;
        }

        return $this->canAccessModule($location['module_id'], $userId);
    }

    public function canAccessModule(int $moduleId, int $userId): bool
    {
        $courseId = $this->resolveCourseIdForModule($moduleId, $userId);
        $outline = $courseId !== null
            ? $this->courseService->getCourseOutline($courseId, $userId)
            : null;

        if ($outline === null) {
            return false;
        }

        $context = $this->buildContext($outline, $userId);

        foreach ($outline['modules'] as $module) {
            if ($module->id === $moduleId) {
                return true;
            }

            if (!$this->isModuleCleared($module->id, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array{course: \App\Models\Course, modules: array<int, Module>, nuggetsByModule: array<int, array<int, Nugget>>} $outline
     * @return array{
     *     modules: array<int, Module>,
     *     nuggetsByModule: array<int, array<int, Nugget>>,
     *     video_nuggets: array<int, Nugget>,
     *     progress_by_nugget: array<int, array<string, mixed>>,
     *     quizzes_by_module: array<int, Quiz>,
     *     latest_attempts: array<int, array<string, mixed>>
     * }
     */
    public function buildContext(array $outline, int $userId): array
    {
        $videoNuggets = $this->collectVideoNuggets($outline['modules'], $outline['nuggetsByModule']);
        $nuggetIds = array_map(static fn (Nugget $nugget): int => $nugget->id, $videoNuggets);
        $moduleIds = array_map(static fn (Module $module): int => $module->id, $outline['modules']);
        $quizzesByModule = $this->quizRepo->listByModuleIds($moduleIds);
        $quizIds = array_values(array_map(static fn (Quiz $quiz): int => $quiz->id, array_filter($quizzesByModule)));

        return [
            'modules' => $outline['modules'],
            'nuggetsByModule' => $outline['nuggetsByModule'],
            'video_nuggets' => $videoNuggets,
            'progress_by_nugget' => $this->progressRepo->listByUserAndNuggetIds($userId, $nuggetIds),
            'quizzes_by_module' => $quizzesByModule,
            'latest_attempts' => $this->attemptRepo->findLatestByUserAndQuizIds($userId, $quizIds),
        ];
    }

    /**
     * @param array{
     *     nuggetsByModule: array<int, array<int, Nugget>>,
     *     progress_by_nugget: array<int, array<string, mixed>>,
     *     quizzes_by_module: array<int, Quiz>,
     *     latest_attempts: array<int, array<string, mixed>>
     * } $context
     */
    public function isModuleCleared(int $moduleId, array $context): bool
    {
        $quiz = $context['quizzes_by_module'][$moduleId] ?? null;
        $videoNugget = $this->findVideoNugget($context['nuggetsByModule'][$moduleId] ?? []);

        if ($quiz !== null) {
            return $this->resolveQuizState($quiz, $context['latest_attempts']) === 'passed';
        }

        if ($videoNugget !== null) {
            $progress = $context['progress_by_nugget'][$videoNugget->id] ?? null;

            return ($progress['status'] ?? '') === 'completed';
        }

        return true;
    }

    /**
     * @param array<int, array{id: int, quiz_id: int, score_pct: int, completed_at: string}> $latestAttempts
     */
    public function resolveQuizState(?Quiz $quiz, array $latestAttempts): ?string
    {
        if ($quiz === null) {
            return null;
        }

        $attempt = $latestAttempts[$quiz->id] ?? null;

        if ($attempt === null) {
            return 'not_started';
        }

        return $attempt['score_pct'] >= $quiz->passingScorePct ? 'passed' : 'failed';
    }

    /**
     * @return array{course_id: int, module_id: int}|null
     */
    private function findNuggetLocation(int $nuggetId, int $userId): ?array
    {
        foreach ($this->courseService->listEnrolledCourses($userId) as $course) {
            $outline = $this->courseService->getCourseOutline($course->id, $userId);

            if ($outline === null) {
                continue;
            }

            foreach ($outline['nuggetsByModule'] as $moduleId => $nuggets) {
                foreach ($nuggets as $nugget) {
                    if ($nugget->id === $nuggetId && $nugget->nuggetType === 'video') {
                        return [
                            'course_id' => $course->id,
                            'module_id' => (int) $moduleId,
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function resolveCourseIdForModule(int $moduleId, int $userId): ?int
    {
        foreach ($this->courseService->listEnrolledCourses($userId) as $course) {
            $outline = $this->courseService->getCourseOutline($course->id, $userId);

            if ($outline === null) {
                continue;
            }

            foreach ($outline['modules'] as $module) {
                if ($module->id === $moduleId) {
                    return $course->id;
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, Module> $modules
     * @param array<int, array<int, Nugget>> $nuggetsByModule
     * @return array<int, Nugget>
     */
    private function collectVideoNuggets(array $modules, array $nuggetsByModule): array
    {
        $videoNuggets = [];

        foreach ($modules as $module) {
            foreach ($nuggetsByModule[$module->id] ?? [] as $nugget) {
                if ($nugget->nuggetType === 'video') {
                    $videoNuggets[] = $nugget;
                }
            }
        }

        return $videoNuggets;
    }

    /**
     * @param array<int, Nugget> $nuggets
     */
    private function findVideoNugget(array $nuggets): ?Nugget
    {
        foreach ($nuggets as $nugget) {
            if ($nugget->nuggetType === 'video') {
                return $nugget;
            }
        }

        return null;
    }
}
