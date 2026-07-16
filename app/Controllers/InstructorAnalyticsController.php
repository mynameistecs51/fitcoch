<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AnalyticsService;
use App\Services\AuthService;
use Exception;

class InstructorAnalyticsController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly AnalyticsService $analyticsService,
    ) {
    }

    public function show(Request $request, int $cohortId): Response
    {
        $moduleId = isset($request->query()['module'])
            ? (int) $request->query()['module']
            : null;

        if ($moduleId === 0) {
            $moduleId = null;
        }

        try {
            $panel = $this->analyticsService->buildCohortAnalytics($cohortId, $moduleId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses?error=' . urlencode($e->getMessage()));
        }

        if ($panel === null) {
            return Response::redirect('/instructor/courses?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/analytics/cohort', [
            'title' => __('analytics.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'cohort' => $panel['cohort'],
            'modules' => $panel['modules'],
            'selectedModule' => $panel['selected_module'],
            'quiz' => $panel['quiz'],
            'metrics' => $panel['metrics'],
            'topMisconceptions' => $panel['top_misconceptions'],
            'atRiskLearners' => $panel['at_risk_learners'],
            'learners' => $panel['learners'],
        ]);
    }

    public function apiModuleMetrics(Request $request, int $cohortId, int $moduleId): Response
    {
        try {
            $this->analyticsService->assertInstructorCanViewCohort($cohortId);
            $metrics = $this->analyticsService->computeCohortReadinessMetrics($cohortId, $moduleId);
            $panel = $this->analyticsService->buildCohortAnalytics($cohortId, $moduleId);
        } catch (Exception $e) {
            return Response::apiError('NOT_FOUND', $e->getMessage(), 404);
        }

        if ($panel === null) {
            return Response::apiError('NOT_FOUND', __('analytics.validation.cohort_not_found'), 404);
        }

        return Response::apiSuccess([
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
            'total_enrolled' => $metrics['total_enrolled'],
            'completed_prep' => $metrics['completed_prep'],
            'readiness_ratio' => $metrics['readiness_ratio'],
            'alert_triggered' => $metrics['alert_triggered'],
            'top_misconceptions' => array_map(static function (array $row): array {
                return [
                    'question_id' => $row['question_id'],
                    'question_text' => $row['question_text'],
                    'incorrect_ratio' => $row['incorrect_ratio'],
                ];
            }, $panel['top_misconceptions']),
        ]);
    }
}
