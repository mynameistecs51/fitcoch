<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Module;
use App\Models\Quiz;
use App\Repositories\AnalyticsRepository;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\ReadinessTicketRepository;
use Exception;

class AnalyticsService
{
    private const READINESS_ALERT_THRESHOLD = 0.60;

    public function __construct(
        private readonly AnalyticsRepository $analyticsRepo,
        private readonly CohortRepository $cohortRepo,
        private readonly CourseRepository $courseRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly QuizRepository $quizRepo,
        private readonly ReadinessTicketRepository $ticketRepo,
        private readonly QuizAttemptRepository $attemptRepo,
    ) {
    }

    /**
     * @return array{
     *     course: Course,
     *     cohort: Cohort,
     *     modules: array<int, Module>,
     *     selected_module: Module,
     *     quiz: ?Quiz,
     *     metrics: array{
     *         total_enrolled: int,
     *         completed_prep: int,
     *         readiness_ratio: float,
     *         readiness_pct: int,
     *         alert_triggered: bool
     *     },
     *     top_misconceptions: array<int, array<string, mixed>>,
     *     at_risk_learners: array<int, array<string, mixed>>,
     *     learners: array<int, array<string, mixed>>
     * }|null
     */
    public function buildCohortAnalytics(int $cohortId, ?int $moduleId = null): ?array
    {
        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null) {
            return null;
        }

        $course = $this->courseRepo->findById($cohort->courseId);

        if ($course === null) {
            return null;
        }

        $modules = $this->moduleRepo->listByCourseId($course->id);

        if ($modules === []) {
            return null;
        }

        $selectedModule = $this->resolveSelectedModule($modules, $moduleId);
        $metrics = $this->computeCohortReadinessMetrics($cohortId, $selectedModule->id);
        $quizzesByModule = $this->quizRepo->listByModuleIds(array_map(
            static fn (Module $module): int => $module->id,
            $modules,
        ));
        $quiz = $quizzesByModule[$selectedModule->id]
            ?? $this->quizRepo->findReadinessByModuleId($selectedModule->id);

        $topMisconceptions = $quiz !== null
            ? $this->analyticsRepo->findTopMisconceptions($cohortId, $quiz->id)
            : [];

        $learners = $this->ticketRepo->listEnrollmentStatuses($cohortId, $selectedModule->id);

        if ($quiz !== null) {
            $latestAttempts = $this->attemptRepo->findLatestByCohortAndQuizIds($cohortId, [$quiz->id]);

            foreach ($learners as $index => $row) {
                $userId = (int) $row['user_id'];
                $attempt = $latestAttempts[$userId][$quiz->id] ?? null;
                $latestScore = $attempt['score_pct'] ?? null;
                $learners[$index]['latest_score'] = $latestScore;
                $learners[$index]['quiz_passed'] = $latestScore !== null && $latestScore >= $quiz->passingScorePct;
            }
        }

        $atRiskLearners = $this->filterAtRiskLearners($learners);

        return [
            'course' => $course,
            'cohort' => $cohort,
            'modules' => $modules,
            'selected_module' => $selectedModule,
            'quiz' => $quiz,
            'metrics' => $metrics,
            'top_misconceptions' => $topMisconceptions,
            'at_risk_learners' => $atRiskLearners,
            'learners' => $learners,
        ];
    }

    /**
     * @return array{
     *     cohort_id: int,
     *     module_id: int,
     *     total_enrolled: int,
     *     completed_prep: int,
     *     readiness_ratio: float,
     *     readiness_pct: int,
     *     alert_triggered: bool
     * }
     */
    public function computeCohortReadinessMetrics(int $cohortId, int $moduleId): array
    {
        $totalEnrolled = $this->analyticsRepo->getTotalEnrolledCount($cohortId);
        $completedPrep = $this->analyticsRepo->getCompletedPrepCount($cohortId, $moduleId);
        $ratio = $totalEnrolled > 0 ? $completedPrep / $totalEnrolled : 0.0;

        return [
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
            'total_enrolled' => $totalEnrolled,
            'completed_prep' => $completedPrep,
            'readiness_ratio' => round($ratio, 4),
            'readiness_pct' => (int) round($ratio * 100),
            'alert_triggered' => $ratio < self::READINESS_ALERT_THRESHOLD,
        ];
    }

    public function assertInstructorCanViewCohort(int $cohortId): Cohort
    {
        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null) {
            throw new Exception(__('analytics.validation.cohort_not_found'));
        }

        return $cohort;
    }

    /**
     * @param array<int, Module> $modules
     */
    private function resolveSelectedModule(array $modules, ?int $moduleId): Module
    {
        if ($moduleId !== null) {
            foreach ($modules as $module) {
                if ($module->id === $moduleId) {
                    return $module;
                }
            }
        }

        return $modules[0];
    }

    /**
     * @param array<int, array<string, mixed>> $learners
     * @return array<int, array<string, mixed>>
     */
    private function filterAtRiskLearners(array $learners): array
    {
        $atRisk = [];

        foreach ($learners as $row) {
            $status = (string) ($row['status'] ?? 'locked');
            $quizPassed = (bool) ($row['quiz_passed'] ?? false);
            $isPrepared = in_array($status, ['unlocked', 'overridden'], true) || $quizPassed;

            if (!$isPrepared) {
                $atRisk[] = $row;
            }
        }

        return array_slice($atRisk, 0, 10);
    }
}
