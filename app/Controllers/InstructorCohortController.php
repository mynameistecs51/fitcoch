<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\CohortService;
use App\Services\ValidationException;
use Exception;

class InstructorCohortController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CohortService $cohortService,
    ) {
    }

    public function index(Request $request, int $courseId): Response
    {
        $panel = $this->cohortService->getCourseCohortsPanel($courseId);

        if ($panel === null) {
            return Response::redirect('/instructor/courses?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/cohorts/index', [
            'title' => __('cohorts.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'cohorts' => $panel['cohorts'],
            'availableLearners' => $panel['available_learners'],
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function store(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=csrf');
        }

        try {
            $this->cohortService->createCohort($courseId, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=validation');
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?success=created');
    }

    public function update(Request $request, int $courseId, int $cohortId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=csrf');
        }

        try {
            $this->cohortService->updateCohort($cohortId, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=validation');
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?success=updated');
    }

    public function enroll(Request $request, int $courseId, int $cohortId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=csrf');
        }

        $learnerId = (int) $request->input('user_id');

        try {
            $this->cohortService->enrollLearner($cohortId, $learnerId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?success=enrolled');
    }

    public function drop(Request $request, int $courseId, int $cohortId, int $learnerId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=csrf');
        }

        try {
            $this->cohortService->dropLearner($cohortId, $learnerId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/cohorts?success=dropped');
    }
}
