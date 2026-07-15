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
                'url' => url('/nuggets/' . $nugget->id),
                'duration_label' => $this->formatDuration($nugget->durationSeconds),
                'quiz' => $moduleQuiz,
                'quiz_url' => $moduleQuiz !== null ? url('/quizzes/' . $moduleQuiz->id) : null,
                'quiz_state' => $quizState,
            ];

            $previousModuleId = $nugget->moduleId;
        }

        if ($activeNuggetId === null && $activeQuizId !== null) {
            foreach ($lessons as $index => $lesson) {
                if (($lesson['quiz']?->id ?? null) === $activeQuizId) {
                    $lessons[$index]['state'] = 'current';
                    break;
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

        foreach ($navigation['lessons'] as $lesson) {
            if ($lesson['state'] === 'current') {
                return $lesson['nugget']->id;
            }
        }

        foreach ($navigation['lessons'] as $lesson) {
            if ($lesson['state'] === 'available') {
                return $lesson['nugget']->id;
            }
        }

        foreach ($navigation['lessons'] as $lesson) {
            if ($lesson['state'] !== 'locked') {
                return $lesson['nugget']->id;
            }
        }

        return $navigation['lessons'][0]['nugget']->id;
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
