<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;

class LessonNavigationService
{
    public function __construct(
        private readonly CourseService $courseService,
        private readonly LessonUnlockService $unlockService,
    ) {
    }

    /**
     * @return array{
     *     course: Course,
     *     module: Module,
     *     lessons: array<int, array{
     *         nugget: Nugget,
     *         module: Module,
     *         state: string,
     *         progress_percentage: int,
     *         url: string,
     *         duration_label: string,
     *         quiz: ?Quiz,
     *         quiz_url: ?string,
     *         quiz_state: ?string
     *     }>,
     *     resources: array<int, array{title: string, url: string}>,
     *     overall_progress: int,
     *     lesson_count: int
     * }|null
     */
    public function buildForLearner(
        int $courseId,
        int $userId,
        int $moduleId,
        ?int $activeNuggetId = null,
        ?int $activeQuizId = null,
    ): ?array {
        $outline = $this->courseService->getCourseOutline($courseId, $userId);

        if ($outline === null) {
            return null;
        }

        $module = null;

        foreach ($outline['modules'] as $courseModule) {
            if ($courseModule->id === $moduleId) {
                $module = $courseModule;
                break;
            }
        }

        if ($module === null) {
            return null;
        }

        $context = $this->unlockService->buildContext($outline, $userId);
        $canAccess = true;
        $previousModuleId = null;
        $lessons = [];
        $progressTotal = 0;

        foreach ($context['video_nuggets'] as $nugget) {
            if ($previousModuleId !== null && $nugget->moduleId !== $previousModuleId) {
                if (!$this->unlockService->isModuleCleared($previousModuleId, $context)) {
                    $canAccess = false;
                }
            }

            $nuggetModule = $this->findModule($outline['modules'], $nugget->moduleId);
            $progress = $context['progress_by_nugget'][$nugget->id] ?? null;
            $progressPercentage = (int) ($progress['progress_percentage'] ?? 0);
            $isCompleted = ($progress['status'] ?? '') === 'completed';
            $moduleQuiz = $context['quizzes_by_module'][$nugget->moduleId] ?? null;
            $quizState = $this->unlockService->resolveQuizState($moduleQuiz, $context['latest_attempts']);

            if ($activeNuggetId === $nugget->id) {
                $state = 'current';
            } elseif (!$canAccess) {
                $state = 'locked';
            } elseif ($isCompleted || $quizState === 'passed') {
                $state = 'completed';
            } else {
                $state = 'available';
            }

            $moduleProgress = $isCompleted || $quizState === 'passed'
                ? 100
                : max($progressPercentage, $quizState === 'failed' ? 50 : 0);
            $progressTotal += $moduleProgress;

            $lessons[] = [
                'nugget' => $nugget,
                'module' => $nuggetModule ?? $module,
                'state' => $state,
                'progress_percentage' => $progressPercentage,
                'video_completed' => $isCompleted,
                'url' => url('/nuggets/' . $nugget->id),
                'duration_label' => $this->formatDuration($nugget->durationSeconds),
                'quiz' => $moduleQuiz,
                'quiz_url' => $moduleQuiz !== null ? url('/quizzes/' . $moduleQuiz->id) : null,
                'quiz_state' => $quizState,
            ];

            $previousModuleId = $nugget->moduleId;
        }

        $this->appendQuizOnlyModules($lessons, $outline, $context, $activeQuizId);

        foreach ($lessons as $lesson) {
            if (empty($lesson['quiz_only'])) {
                continue;
            }

            $quizState = (string) ($lesson['quiz_state'] ?? 'not_started');
            $progressTotal += $quizState === 'passed'
                ? 100
                : ($quizState === 'failed' ? 50 : 0);
        }

        if ($activeNuggetId === null && $activeQuizId !== null) {
            foreach ($lessons as $index => $lesson) {
                if (($lesson['quiz']?->id ?? null) === $activeQuizId) {
                    $lessons[$index]['state'] = 'current';
                    break;
                }

                if (!empty($lesson['quiz_only'])) {
                    continue;
                }

                if ($lesson['nugget']->moduleId === $moduleId) {
                    $lessons[$index]['state'] = 'current';
                    break;
                }
            }
        } elseif ($activeNuggetId === null) {
            $hasCurrent = false;

            foreach ($lessons as $lesson) {
                if ($lesson['state'] === 'current') {
                    $hasCurrent = true;
                    break;
                }
            }

            if (!$hasCurrent) {
                foreach ($lessons as $index => $lesson) {
                    if ($lesson['state'] === 'available') {
                        $lessons[$index]['state'] = 'current';
                        break;
                    }
                }
            }
        }

        $lessonCount = count($lessons);
        $overallProgress = $lessonCount > 0 ? (int) round($progressTotal / $lessonCount) : 0;

        return [
            'course' => $outline['course'],
            'module' => $module,
            'lessons' => $lessons,
            'resources' => $this->collectResources($outline['nuggetsByModule'][$moduleId] ?? []),
            'overall_progress' => $overallProgress,
            'lesson_count' => $lessonCount,
        ];
    }

    public function canAccessNugget(int $nuggetId, int $userId): bool
    {
        return $this->unlockService->canAccessNugget($nuggetId, $userId);
    }

    public function findResumeNuggetId(int $courseId, int $userId): ?int
    {
        $url = $this->findResumeLessonUrl($courseId, $userId);

        if ($url === null || !preg_match('#/nuggets/(\d+)#', $url, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    public function findResumeLessonUrl(int $courseId, int $userId): ?string
    {
        $outline = $this->courseService->getCourseOutline($courseId, $userId);

        if ($outline === null || $outline['modules'] === []) {
            return null;
        }

        $navigation = $this->buildForLearner(
            $courseId,
            $userId,
            $outline['modules'][0]->id,
        );

        if ($navigation === null || $navigation['lessons'] === []) {
            return null;
        }

        foreach (['current', 'available'] as $targetState) {
            foreach ($navigation['lessons'] as $lesson) {
                if (($lesson['state'] ?? '') !== $targetState) {
                    continue;
                }

                $resumeUrl = $this->resolveLessonResumeUrl($lesson);

                if ($resumeUrl !== null) {
                    return $resumeUrl;
                }
            }
        }

        foreach ($navigation['lessons'] as $lesson) {
            if (($lesson['state'] ?? '') === 'locked') {
                continue;
            }

            $resumeUrl = $this->resolveLessonResumeUrl($lesson);

            if ($resumeUrl !== null) {
                return $resumeUrl;
            }
        }

        return $this->resolveLessonResumeUrl($navigation['lessons'][0]);
    }

    /**
     * @param array<string, mixed> $lesson
     */
    private function resolveLessonResumeUrl(array $lesson): ?string
    {
        if (!empty($lesson['quiz_only'])) {
            return $lesson['quiz_url'] ?? null;
        }

        $videoCompleted = !empty($lesson['video_completed']);
        $quizState = (string) ($lesson['quiz_state'] ?? 'not_started');
        $quizUrl = $lesson['quiz_url'] ?? null;

        if ($videoCompleted && $quizUrl !== null && $quizState !== 'passed') {
            return $quizUrl;
        }

        return $lesson['url'] ?? null;
    }

    /**
     * @return array{
     *     course: Course,
     *     modules: array<int, Module>,
     *     nuggets_by_module: array<int, array<int, Nugget>>,
     *     lessons_by_module: array<int, array<int, array<string, mixed>>>,
     *     overall_progress: int
     * }|null
     */
    public function buildSyllabusSummary(int $courseId, int $userId): ?array
    {
        $outline = $this->courseService->getCourseOutline($courseId, $userId);

        if ($outline === null || $outline['modules'] === []) {
            return null;
        }

        $navigation = $this->buildForLearner(
            $courseId,
            $userId,
            $outline['modules'][0]->id,
        );

        if ($navigation === null) {
            return null;
        }

        $lessonsByModule = [];

        foreach ($navigation['lessons'] as $lesson) {
            $moduleId = $lesson['module']->id;
            $lessonsByModule[$moduleId][] = $lesson;
        }

        return [
            'course' => $outline['course'],
            'modules' => $outline['modules'],
            'nuggets_by_module' => $outline['nuggetsByModule'],
            'lessons_by_module' => $lessonsByModule,
            'overall_progress' => $navigation['overall_progress'],
        ];
    }

    /**
     * @param array<int, Nugget> $nuggets
     * @return array<int, array{title: string, url: string}>
     */
    private function collectResources(array $nuggets): array
    {
        $resources = [];

        foreach ($nuggets as $nugget) {
            if ($nugget->nuggetType !== 'reading' || ($nugget->contentUrl ?? '') === '') {
                continue;
            }

            $resources[] = [
                'title' => $nugget->title,
                'url' => $nugget->contentUrl,
            ];
        }

        return $resources;
    }

    /**
     * @param array<int, Module> $modules
     */
    private function findModule(array $modules, int $moduleId): ?Module
    {
        foreach ($modules as $module) {
            if ($module->id === $moduleId) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $lessons
     * @param array{
     *     modules: array<int, Module>,
     *     nuggetsByModule: array<int, array<int, Nugget>>
     * } $outline
     * @param array{
     *     quizzes_by_module: array<int, Quiz>,
     *     latest_attempts: array<int, array<string, mixed>>
     * } $context
     */
    private function appendQuizOnlyModules(
        array &$lessons,
        array $outline,
        array $context,
        ?int $activeQuizId = null,
    ): void {
        $moduleIdsWithVideo = [];

        foreach ($lessons as $lesson) {
            if (!empty($lesson['quiz_only'])) {
                continue;
            }

            $moduleIdsWithVideo[$lesson['module']->id] = true;
        }

        $canAccess = true;
        $previousModuleId = null;

        foreach ($outline['modules'] as $module) {
            if ($previousModuleId !== null && !$this->unlockService->isModuleCleared($previousModuleId, $context)) {
                $canAccess = false;
            }

            if (isset($moduleIdsWithVideo[$module->id])) {
                $previousModuleId = $module->id;
                continue;
            }

            $quiz = $context['quizzes_by_module'][$module->id] ?? null;

            if ($quiz === null) {
                $previousModuleId = $module->id;
                continue;
            }

            $quizState = $this->unlockService->resolveQuizState($quiz, $context['latest_attempts']);
            $state = !$canAccess ? 'locked' : ($quizState === 'passed' ? 'completed' : 'available');

            if ($activeQuizId === $quiz->id) {
                $state = 'current';
            }

            $lessons[] = [
                'quiz_only' => true,
                'module' => $module,
                'state' => $state,
                'progress_percentage' => $quizState === 'passed' ? 100 : ($quizState === 'failed' ? 50 : 0),
                'video_completed' => false,
                'url' => null,
                'duration_label' => '',
                'quiz' => $quiz,
                'quiz_url' => url('/quizzes/' . $quiz->id),
                'quiz_state' => $quizState,
            ];

            $previousModuleId = $module->id;
        }
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            $seconds = 180;
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds) . ' ' . __('lesson.minutes');
    }
}
