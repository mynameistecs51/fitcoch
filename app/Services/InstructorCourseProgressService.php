<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\CohortRepository;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;

class InstructorCourseProgressService
{
    public function __construct(
        private readonly CourseService $courseService,
        private readonly QuizService $quizService,
        private readonly CohortRepository $cohortRepo,
        private readonly NuggetProgressRepository $progressRepo,
        private readonly QuizAttemptRepository $attemptRepo,
    ) {
    }

    /**
     * @param array<int, int> $courseIds
     * @return array<int, int>
     */
    public function countEnrollmentsByCourseIds(array $courseIds): array
    {
        return $this->cohortRepo->countActiveEnrollmentsByCourseIds($courseIds);
    }

    /**
     * @return array{
     *     course: Course,
     *     cohort: ?Cohort,
     *     summary: array{
     *         total_enrolled: int,
     *         avg_progress_pct: int,
     *         lessons_completed_avg: float,
     *         lessons_total: int,
     *         quizzes_passed_avg: float,
     *         quizzes_total: int,
     *         average_quiz_score: ?int
     *     },
     *     modules: array<int, array{
     *         module: Module,
     *         video_nugget: ?Nugget,
     *         quiz: ?Quiz,
     *         learners_passed: int,
     *         learners_in_progress: int,
     *         learners_not_started: int,
     *         learners_failed: int,
     *         avg_video_progress: int,
     *         quiz_pass_rate: ?int
     *     }>,
     *     learners: array<int, array{
     *         user_id: int,
     *         first_name: string,
     *         last_name: string,
     *         email: string,
     *         enrolled_at: string,
     *         progress_pct: int,
     *         lessons_completed: int,
     *         lessons_total: int,
     *         quizzes_passed: int,
     *         quizzes_total: int,
     *         average_quiz_score: ?int,
     *         modules: array<int, array{
     *             module: Module,
     *             status: string,
     *             latest_score: ?int,
     *             video_progress: ?int
     *         }>
     *     }>
     * }|null
     */
    public function buildCourseReport(int $courseId): ?array
    {
        $outline = $this->courseService->getCourseForInstructor($courseId);

        if ($outline === null) {
            return null;
        }

        $course = $outline['course'];
        $modules = $outline['modules'];
        $nuggetsByModule = $outline['nuggetsByModule'];
        $cohorts = $this->cohortRepo->listByCourseId($courseId);
        $cohort = $cohorts[0] ?? null;

        $moduleIds = array_map(static fn (Module $module): int => $module->id, $modules);
        $quizzesByModule = $this->quizService->listQuizzesByModuleIds($moduleIds);
        $quizIds = array_values(array_map(
            static fn (Quiz $quiz): int => $quiz->id,
            array_filter($quizzesByModule)
        ));

        $videoNuggets = $this->collectVideoNuggets($modules, $nuggetsByModule);
        $nuggetIds = array_map(static fn (Nugget $nugget): int => $nugget->id, $videoNuggets);
        $lessonsTotal = count($videoNuggets);
        $quizzesTotal = count(array_filter($quizzesByModule));

        $enrollments = $cohort !== null ? $this->cohortRepo->listActiveEnrollments($cohort->id) : [];
        $progressByUser = $cohort !== null
            ? $this->progressRepo->listByCohortAndNuggetIds($cohort->id, $nuggetIds)
            : [];
        $attemptsByUser = $cohort !== null
            ? $this->attemptRepo->findLatestByCohortAndQuizIds($cohort->id, $quizIds)
            : [];

        $moduleTemplate = $this->buildModuleTemplate($modules, $nuggetsByModule, $quizzesByModule);
        $moduleAggregates = [];

        foreach ($moduleTemplate as $template) {
            $moduleAggregates[$template['module']->id] = [
                'module' => $template['module'],
                'video_nugget' => $template['video_nugget'],
                'quiz' => $template['quiz'],
                'learners_passed' => 0,
                'learners_in_progress' => 0,
                'learners_not_started' => 0,
                'learners_failed' => 0,
                'video_progress_total' => 0,
                'video_progress_count' => 0,
                'quiz_passed_count' => 0,
                'quiz_attempted_count' => 0,
            ];
        }

        $learners = [];
        $progressTotal = 0;
        $lessonsCompletedTotal = 0;
        $quizzesPassedTotal = 0;
        $quizScoreTotal = 0;
        $quizScoreCount = 0;

        foreach ($enrollments as $enrollment) {
            $userId = (int) $enrollment['user_id'];
            $progressByNugget = $progressByUser[$userId] ?? [];
            $latestAttempts = $attemptsByUser[$userId] ?? [];

            $learnerRow = $this->buildLearnerRow(
                $enrollment,
                $moduleTemplate,
                $progressByNugget,
                $latestAttempts,
                $lessonsTotal,
                $quizzesTotal
            );

            $learners[] = $learnerRow;
            $progressTotal += $learnerRow['progress_pct'];
            $lessonsCompletedTotal += $learnerRow['lessons_completed'];
            $quizzesPassedTotal += $learnerRow['quizzes_passed'];

            if ($learnerRow['average_quiz_score'] !== null) {
                $quizScoreTotal += $learnerRow['average_quiz_score'];
                $quizScoreCount++;
            }

            foreach ($learnerRow['modules'] as $moduleRow) {
                $moduleId = $moduleRow['module']->id;
                $aggregate = &$moduleAggregates[$moduleId];
                $status = $moduleRow['status'];

                match ($status) {
                    'passed' => $aggregate['learners_passed']++,
                    'failed' => $aggregate['learners_failed']++,
                    'in_progress' => $aggregate['learners_in_progress']++,
                    default => $aggregate['learners_not_started']++,
                };

                if ($moduleRow['video_progress'] !== null) {
                    $aggregate['video_progress_total'] += $moduleRow['video_progress'];
                    $aggregate['video_progress_count']++;
                }

                if ($moduleRow['latest_score'] !== null && $aggregate['quiz'] !== null) {
                    $aggregate['quiz_attempted_count']++;

                    if ($moduleRow['latest_score'] >= $aggregate['quiz']->passingScorePct) {
                        $aggregate['quiz_passed_count']++;
                    }
                }
            }
        }

        $enrolledCount = count($learners);
        $moduleRows = [];

        foreach ($moduleAggregates as $aggregate) {
            $moduleRows[] = [
                'module' => $aggregate['module'],
                'video_nugget' => $aggregate['video_nugget'],
                'quiz' => $aggregate['quiz'],
                'learners_passed' => $aggregate['learners_passed'],
                'learners_in_progress' => $aggregate['learners_in_progress'],
                'learners_not_started' => $aggregate['learners_not_started'],
                'learners_failed' => $aggregate['learners_failed'],
                'avg_video_progress' => $aggregate['video_progress_count'] > 0
                    ? (int) round($aggregate['video_progress_total'] / $aggregate['video_progress_count'])
                    : 0,
                'quiz_pass_rate' => $aggregate['quiz'] !== null && $aggregate['quiz_attempted_count'] > 0
                    ? (int) round(($aggregate['quiz_passed_count'] / $aggregate['quiz_attempted_count']) * 100)
                    : null,
            ];
        }

        return [
            'course' => $course,
            'cohort' => $cohort,
            'summary' => [
                'total_enrolled' => $enrolledCount,
                'avg_progress_pct' => $enrolledCount > 0 ? (int) round($progressTotal / $enrolledCount) : 0,
                'lessons_completed_avg' => $enrolledCount > 0
                    ? round($lessonsCompletedTotal / $enrolledCount, 1)
                    : 0.0,
                'lessons_total' => $lessonsTotal,
                'quizzes_passed_avg' => $enrolledCount > 0
                    ? round($quizzesPassedTotal / $enrolledCount, 1)
                    : 0.0,
                'quizzes_total' => $quizzesTotal,
                'average_quiz_score' => $quizScoreCount > 0
                    ? (int) round($quizScoreTotal / $quizScoreCount)
                    : null,
            ],
            'modules' => $moduleRows,
            'learners' => $learners,
        ];
    }

    /**
     * @param array<int, Module> $modules
     * @param array<int, array<int, Nugget>> $nuggetsByModule
     * @param array<int, Quiz> $quizzesByModule
     * @return array<int, array{module: Module, video_nugget: ?Nugget, quiz: ?Quiz}>
     */
    private function buildModuleTemplate(array $modules, array $nuggetsByModule, array $quizzesByModule): array
    {
        $template = [];

        foreach ($modules as $module) {
            $videoNugget = null;

            foreach ($nuggetsByModule[$module->id] ?? [] as $nugget) {
                if ($nugget->nuggetType === 'video') {
                    $videoNugget = $nugget;
                    break;
                }
            }

            $template[] = [
                'module' => $module,
                'video_nugget' => $videoNugget,
                'quiz' => $quizzesByModule[$module->id] ?? null,
            ];
        }

        return $template;
    }

    /**
     * @param array{
     *     user_id: int|string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     enrolled_at: string
     * } $enrollment
     * @param array<int, array{module: Module, video_nugget: ?Nugget, quiz: ?Quiz}> $moduleTemplate
     * @param array<int, array<string, mixed>> $progressByNugget
     * @param array<int, array{id: int, quiz_id: int, score_pct: int, completed_at: string}> $latestAttempts
     * @return array{
     *     user_id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     enrolled_at: string,
     *     progress_pct: int,
     *     lessons_completed: int,
     *     lessons_total: int,
     *     quizzes_passed: int,
     *     quizzes_total: int,
     *     average_quiz_score: ?int,
     *     modules: array<int, array{
     *         module: Module,
     *         status: string,
     *         latest_score: ?int,
     *         video_progress: ?int
     *     }>
     * }
     */
    private function buildLearnerRow(
        array $enrollment,
        array $moduleTemplate,
        array $progressByNugget,
        array $latestAttempts,
        int $lessonsTotal,
        int $quizzesTotal,
    ): array {
        $lessonsCompleted = 0;
        $quizzesPassed = 0;
        $progressTotal = 0;
        $quizScoreTotal = 0;
        $quizScoreCount = 0;
        $moduleRows = [];

        foreach ($moduleTemplate as $template) {
            $module = $template['module'];
            $videoNugget = $template['video_nugget'];
            $quiz = $template['quiz'];
            $status = 'not_started';
            $videoProgress = null;
            $latestScore = null;

            if ($videoNugget !== null) {
                $progress = $progressByNugget[$videoNugget->id] ?? null;
                $videoProgress = (int) ($progress['progress_percentage'] ?? 0);
                $isCompleted = ($progress['status'] ?? '') === 'completed';

                if ($isCompleted) {
                    $lessonsCompleted++;
                    $progressTotal += 100;
                    $status = 'in_progress';
                } else {
                    $progressTotal += $videoProgress;

                    if ($videoProgress > 0) {
                        $status = 'in_progress';
                    }
                }
            }

            if ($quiz !== null) {
                $attempt = $latestAttempts[$quiz->id] ?? null;

                if ($attempt !== null) {
                    $latestScore = $attempt['score_pct'];
                    $quizScoreTotal += $latestScore;
                    $quizScoreCount++;
                    $passed = $latestScore >= $quiz->passingScorePct;

                    if ($passed) {
                        $quizzesPassed++;
                        $status = 'passed';
                    } else {
                        $status = 'failed';
                    }
                }
            } elseif ($videoNugget !== null && ($progressByNugget[$videoNugget->id]['status'] ?? '') === 'completed') {
                $status = 'passed';
            }

            $moduleRows[] = [
                'module' => $module,
                'status' => $status,
                'latest_score' => $latestScore,
                'video_progress' => $videoProgress,
            ];
        }

        return [
            'user_id' => (int) $enrollment['user_id'],
            'first_name' => (string) $enrollment['first_name'],
            'last_name' => (string) $enrollment['last_name'],
            'email' => (string) $enrollment['email'],
            'enrolled_at' => (string) $enrollment['enrolled_at'],
            'progress_pct' => $lessonsTotal > 0 ? (int) round($progressTotal / $lessonsTotal) : 0,
            'lessons_completed' => $lessonsCompleted,
            'lessons_total' => $lessonsTotal,
            'quizzes_passed' => $quizzesPassed,
            'quizzes_total' => $quizzesTotal,
            'average_quiz_score' => $quizScoreCount > 0
                ? (int) round($quizScoreTotal / $quizScoreCount)
                : null,
            'modules' => $moduleRows,
        ];
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
}
