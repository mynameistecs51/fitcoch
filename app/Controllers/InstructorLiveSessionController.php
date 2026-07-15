<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\LiveSessionService;
use App\Services\ValidationException;
use Exception;

class InstructorLiveSessionController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LiveSessionService $liveSessionService,
    ) {
    }

    public function index(Request $request, int $courseId, int $moduleId): Response
    {
        $panel = $this->liveSessionService->getInstructorPanel($courseId, $moduleId);

        if ($panel === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/live-sessions/index', [
            'title' => __('live.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'cohort' => $panel['cohort'],
            'module' => $panel['module'],
            'sessions' => $panel['sessions'],
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
            'errors' => [],
            'form' => [],
        ]);
    }

    public function store(Request $request, int $courseId, int $moduleId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=csrf');
        }

        try {
            $this->liveSessionService->createSession($courseId, $moduleId, $request->all());
        } catch (ValidationException $e) {
            return $this->renderFormWithErrors($courseId, $moduleId, $e->errors(), $request);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?success=created');
    }

    public function activate(Request $request, int $courseId, int $moduleId, int $sessionId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=csrf');
        }

        try {
            $this->liveSessionService->activateSession($courseId, $moduleId, $sessionId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?success=activated');
    }

    public function complete(Request $request, int $courseId, int $moduleId, int $sessionId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=csrf');
        }

        try {
            $this->liveSessionService->completeSession($courseId, $moduleId, $sessionId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/live-sessions?success=completed');
    }

    /** @param array<string, array<int, string>> $errors */
    private function renderFormWithErrors(int $courseId, int $moduleId, array $errors, Request $request): Response
    {
        $panel = $this->liveSessionService->getInstructorPanel($courseId, $moduleId);

        if ($panel === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/live-sessions/index', [
            'title' => __('live.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'cohort' => $panel['cohort'],
            'module' => $panel['module'],
            'sessions' => $panel['sessions'],
            'success' => null,
            'error' => null,
            'errors' => $errors,
            'form' => [
                'title' => (string) $request->input('title', ''),
                'start_time' => (string) $request->input('start_time', ''),
                'end_time' => (string) $request->input('end_time', ''),
            ],
        ]);
    }
}
