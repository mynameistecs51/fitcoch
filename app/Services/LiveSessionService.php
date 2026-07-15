<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\LiveAttendance;
use App\Models\LiveSession;
use App\Models\Module;
use App\Models\ReadinessTicket;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\LiveAttendanceRepository;
use App\Repositories\LiveSessionRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\ReadinessTicketRepository;
use Exception;

class LiveSessionService
{
    public function __construct(
        private readonly LiveSessionRepository $sessionRepo,
        private readonly LiveAttendanceRepository $attendanceRepo,
        private readonly ReadinessTicketRepository $ticketRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly CourseRepository $courseRepo,
        private readonly CohortRepository $cohortRepo,
    ) {
    }

    /**
     * @return array{
     *     session: LiveSession,
     *     module: Module,
     *     course: Course,
     *     cohort: Cohort,
     *     ticket: ?ReadinessTicket,
     *     attendance: ?LiveAttendance,
     *     can_join: bool,
     *     is_host: bool
     * }|null
     */
    public function getRoomContext(int $sessionId, int $userId, array $roles): ?array
    {
        $session = $this->sessionRepo->findById($sessionId);

        if ($session === null) {
            return null;
        }

        $context = $this->resolveModuleContext($session->moduleId, $session->cohortId, $userId, $roles);

        if ($context === null) {
            return null;
        }

        $isHost = $this->isHost($roles);
        $ticket = $this->ticketRepo->find($userId, $session->cohortId, $session->moduleId);
        $canJoin = $this->canJoinSession($session, $ticket, $isHost);

        return [
            'session' => $session,
            'module' => $context['module'],
            'course' => $context['course'],
            'cohort' => $context['cohort'],
            'ticket' => $ticket,
            'attendance' => $this->attendanceRepo->find($sessionId, $userId),
            'can_join' => $canJoin,
            'is_host' => $isHost,
        ];
    }

    /**
     * @return array{
     *     course: Course,
     *     cohort: Cohort,
     *     module: Module,
     *     sessions: array<int, LiveSession>
     * }|null
     */
    public function getInstructorPanel(int $courseId, int $moduleId): ?array
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null || $module->courseId !== $courseId) {
            return null;
        }

        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            return null;
        }

        $cohorts = $this->cohortRepo->listByCourseId($courseId);
        $cohort = $cohorts[0] ?? null;

        if ($cohort === null) {
            return null;
        }

        return [
            'course' => $course,
            'cohort' => $cohort,
            'module' => $module,
            'sessions' => $this->sessionRepo->listByModule($moduleId),
        ];
    }

    /** @param array<string, mixed> $data */
    public function createSession(int $courseId, int $moduleId, array $data): LiveSession
    {
        $panel = $this->getInstructorPanel($courseId, $moduleId);

        if ($panel === null) {
            throw new Exception(__('live.validation.module_not_found'));
        }

        $validated = $this->validateSessionInput($data);
        $roomId = $this->generateRoomId($panel['cohort']->id, $moduleId);

        return $this->sessionRepo->create([
            'cohort_id' => $panel['cohort']->id,
            'module_id' => $moduleId,
            'title' => $validated['title'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
            'room_id' => $roomId,
        ]);
    }

    public function activateSession(int $courseId, int $moduleId, int $sessionId): LiveSession
    {
        $session = $this->assertInstructorSession($courseId, $moduleId, $sessionId);

        if ($session->status === 'completed' || $session->status === 'cancelled') {
            throw new Exception(__('live.validation.cannot_activate'));
        }

        return $this->sessionRepo->updateStatus($sessionId, 'active');
    }

    public function completeSession(int $courseId, int $moduleId, int $sessionId): LiveSession
    {
        $session = $this->assertInstructorSession($courseId, $moduleId, $sessionId);

        if ($session->status === 'cancelled') {
            throw new Exception(__('live.validation.cannot_complete'));
        }

        return $this->sessionRepo->updateStatus($sessionId, 'completed');
    }

    /**
     * @return array{session: LiveSession, attendance: LiveAttendance, signaling_token: string}
     */
    public function joinSession(int $sessionId, int $userId, array $roles): array
    {
        $context = $this->getRoomContext($sessionId, $userId, $roles);

        if ($context === null) {
            throw new Exception(__('live.validation.not_found'));
        }

        if (!$context['can_join']) {
            throw new Exception(__('live.validation.gate_blocked'));
        }

        $attendance = $this->attendanceRepo->join($sessionId, $userId);

        return [
            'session' => $context['session'],
            'attendance' => $attendance,
            'signaling_token' => $this->buildSignalingToken($context['session'], $userId, $context['is_host']),
        ];
    }

    public function leaveSession(int $sessionId, int $userId, array $roles): LiveAttendance
    {
        $context = $this->getRoomContext($sessionId, $userId, $roles);

        if ($context === null) {
            throw new Exception(__('live.validation.not_found'));
        }

        return $this->attendanceRepo->leave($sessionId, $userId);
    }

    /**
     * @param array<int, int> $moduleIds
     * @return array<int, array<int, LiveSession>>
     */
    public function listSessionsByModuleIds(array $moduleIds): array
    {
        $sessions = $this->sessionRepo->listByModuleIds($moduleIds);
        $grouped = [];

        foreach ($moduleIds as $moduleId) {
            $grouped[$moduleId] = [];
        }

        foreach ($sessions as $session) {
            $grouped[$session->moduleId][] = $session;
        }

        return $grouped;
    }

    /** @return array<int, array<string, mixed>> */
    public function listSessionRoster(int $sessionId, int $userId, array $roles): array
    {
        $context = $this->getRoomContext($sessionId, $userId, $roles);

        if ($context === null || !$context['is_host']) {
            throw new Exception(__('errors.forbidden'));
        }

        return $this->attendanceRepo->listRosterForSession($sessionId, $context['cohort']->id);
    }

    public function activateSessionById(int $sessionId, int $userId, array $roles): LiveSession
    {
        $context = $this->getRoomContext($sessionId, $userId, $roles);

        if ($context === null || !$context['is_host']) {
            throw new Exception(__('errors.forbidden'));
        }

        if (in_array($context['session']->status, ['completed', 'cancelled'], true)) {
            throw new Exception(__('live.validation.cannot_activate'));
        }

        return $this->sessionRepo->updateStatus($sessionId, 'active');
    }

    public function completeSessionById(int $sessionId, int $userId, array $roles): LiveSession
    {
        $context = $this->getRoomContext($sessionId, $userId, $roles);

        if ($context === null || !$context['is_host']) {
            throw new Exception(__('errors.forbidden'));
        }

        if ($context['session']->status === 'cancelled') {
            throw new Exception(__('live.validation.cannot_complete'));
        }

        return $this->sessionRepo->updateStatus($sessionId, 'completed');
    }

    private function assertInstructorSession(int $courseId, int $moduleId, int $sessionId): LiveSession
    {
        $panel = $this->getInstructorPanel($courseId, $moduleId);

        if ($panel === null) {
            throw new Exception(__('live.validation.module_not_found'));
        }

        $session = $this->sessionRepo->findById($sessionId);

        if ($session === null || $session->moduleId !== $moduleId || $session->cohortId !== $panel['cohort']->id) {
            throw new Exception(__('live.validation.not_found'));
        }

        return $session;
    }

    private function canJoinSession(LiveSession $session, ?ReadinessTicket $ticket, bool $isHost): bool
    {
        if (!$session->isJoinable()) {
            return false;
        }

        if ($isHost) {
            return true;
        }

        return $ticket !== null && $ticket->isOpen();
    }

    /** @param array<int, string> $roles */
    private function isHost(array $roles): bool
    {
        return array_intersect($roles, ['instructor', 'admin']) !== [];
    }

    /**
     * @param array<int, string> $roles
     * @return array{module: Module, course: Course, cohort: Cohort}|null
     */
    private function resolveModuleContext(int $moduleId, int $cohortId, int $userId, array $roles): ?array
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null) {
            return null;
        }

        $course = $this->courseRepo->findById($module->courseId);

        if ($course === null) {
            return null;
        }

        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null || $cohort->courseId !== $course->id) {
            return null;
        }

        if ($this->isHost($roles)) {
            return [
                'module' => $module,
                'course' => $course,
                'cohort' => $cohort,
            ];
        }

        $enrollment = $this->cohortRepo->findActiveEnrollmentForUser($userId, $course->id);

        if ($enrollment === null || $enrollment->id !== $cohortId) {
            return null;
        }

        return [
            'module' => $module,
            'course' => $course,
            'cohort' => $cohort,
        ];
    }

    /** @param array<string, mixed> $data */
    private function validateSessionInput(array $data): array
    {
        $errors = [];
        $title = trim((string) ($data['title'] ?? ''));
        $startTime = trim((string) ($data['start_time'] ?? ''));
        $endTime = trim((string) ($data['end_time'] ?? ''));

        if ($title === '') {
            $errors['title'][] = __('live.validation.title_required');
        }

        if ($startTime === '' || strtotime($startTime) === false) {
            $errors['start_time'][] = __('live.validation.start_time_invalid');
        }

        if ($endTime === '' || strtotime($endTime) === false) {
            $errors['end_time'][] = __('live.validation.end_time_invalid');
        }

        if ($errors === [] && strtotime($endTime) <= strtotime($startTime)) {
            $errors['end_time'][] = __('live.validation.end_before_start');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'title' => $title,
            'start_time' => date('Y-m-d H:i:s', strtotime($startTime)),
            'end_time' => date('Y-m-d H:i:s', strtotime($endTime)),
        ];
    }

    private function generateRoomId(int $cohortId, int $moduleId): string
    {
        do {
            $roomId = sprintf('fitcoch-%d-%d-%s', $cohortId, $moduleId, bin2hex(random_bytes(4)));
        } while ($this->sessionRepo->roomIdExists($roomId));

        return $roomId;
    }

    private function buildSignalingToken(LiveSession $session, int $userId, bool $isHost): string
    {
        $payload = $session->roomId . ':' . $userId . ':' . ($isHost ? 'host' : 'learner');

        return rtrim(strtr(base64_encode(hash('sha256', $payload, true)), '+/', '-_'), '=');
    }
}
