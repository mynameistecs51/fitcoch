<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\LiveSessionService;
use Exception;

class LiveSessionController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LiveSessionService $liveSessionService,
    ) {
    }

    public function show(Request $request, int $id): Response
    {
        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);
        $context = $request->getAttribute('live_context')
            ?? $this->liveSessionService->getRoomContext($id, $user?->id ?? 0, $roles);

        if ($context === null) {
            return Response::view('errors/forbidden', [
                'title' => __('errors.access_denied'),
            ], 403);
        }

        $roster = [];

        if ($context['is_host']) {
            try {
                $roster = $this->liveSessionService->listSessionRoster($id, $user?->id ?? 0, $roles);
            } catch (Exception) {
                $roster = [];
            }
        }

        return Response::view('live/room', [
            'title' => $context['session']->title,
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'session' => $context['session'],
            'module' => $context['module'],
            'course' => $context['course'],
            'cohort' => $context['cohort'],
            'ticket' => $context['ticket'],
            'attendance' => $context['attendance'],
            'isHost' => $context['is_host'],
            'roster' => $roster,
            'joinUrl' => url('/api/v1/live/' . $id . '/join'),
            'leaveUrl' => url('/api/v1/live/' . $id . '/leave'),
            'participantsUrl' => url('/api/v1/live/' . $id . '/participants'),
            'activateUrl' => url('/api/v1/live/' . $id . '/activate'),
            'completeUrl' => url('/api/v1/live/' . $id . '/complete'),
        ]);
    }

    public function apiJoin(Request $request, int $id): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $roles = $this->authService->getUserRoles($userId);

        try {
            $result = $this->liveSessionService->joinSession($id, $userId, $roles);
        } catch (Exception $e) {
            return Response::apiError('LIVE_JOIN_FAILED', $e->getMessage(), 403);
        }

        return Response::apiSuccess([
            'session' => $result['session']->toArray(),
            'attendance' => $result['attendance']->toArray(),
            'signaling_token' => $result['signaling_token'],
            'signaling_mode' => 'stub',
        ]);
    }

    public function apiLeave(Request $request, int $id): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $roles = $this->authService->getUserRoles($userId);

        try {
            $attendance = $this->liveSessionService->leaveSession($id, $userId, $roles);
        } catch (Exception $e) {
            return Response::apiError('LIVE_LEAVE_FAILED', $e->getMessage(), 400);
        }

        return Response::apiSuccess([
            'attendance' => $attendance->toArray(),
        ]);
    }

    public function apiParticipants(Request $request, int $id): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $roles = $this->authService->getUserRoles($userId);

        try {
            $roster = $this->liveSessionService->listSessionRoster($id, $userId, $roles);
        } catch (Exception $e) {
            return Response::apiError('LIVE_ROSTER_FAILED', $e->getMessage(), 403);
        }

        return Response::apiSuccess([
            'participants' => $roster,
            'online_count' => count(array_filter($roster, static fn (array $row): bool => $row['presence'] === 'online')),
        ]);
    }

    public function apiActivate(Request $request, int $id): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $roles = $this->authService->getUserRoles($userId);

        try {
            $session = $this->liveSessionService->activateSessionById($id, $userId, $roles);
        } catch (Exception $e) {
            return Response::apiError('LIVE_ACTIVATE_FAILED', $e->getMessage(), 403);
        }

        return Response::apiSuccess(['session' => $session->toArray()]);
    }

    public function apiComplete(Request $request, int $id): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $roles = $this->authService->getUserRoles($userId);

        try {
            $session = $this->liveSessionService->completeSessionById($id, $userId, $roles);
        } catch (Exception $e) {
            return Response::apiError('LIVE_COMPLETE_FAILED', $e->getMessage(), 403);
        }

        return Response::apiSuccess(['session' => $session->toArray()]);
    }
}
