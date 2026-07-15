<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\LiveSession;
use App\Models\Module;
use App\Models\ReadinessTicket;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\LiveAttendanceRepository;
use App\Repositories\LiveSessionRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\ReadinessTicketRepository;
use App\Services\LiveSessionService;
use PHPUnit\Framework\TestCase;

class LiveSessionServiceTest extends TestCase
{
    private function createService(
        ?LiveSessionRepository $sessionRepo = null,
        ?LiveAttendanceRepository $attendanceRepo = null,
        ?ReadinessTicketRepository $ticketRepo = null,
        ?ModuleRepository $moduleRepo = null,
        ?CourseRepository $courseRepo = null,
        ?CohortRepository $cohortRepo = null,
    ): LiveSessionService {
        return new LiveSessionService(
            $sessionRepo ?? $this->createMock(LiveSessionRepository::class),
            $attendanceRepo ?? $this->createMock(LiveAttendanceRepository::class),
            $ticketRepo ?? $this->createMock(ReadinessTicketRepository::class),
            $moduleRepo ?? $this->createMock(ModuleRepository::class),
            $courseRepo ?? $this->createMock(CourseRepository::class),
            $cohortRepo ?? $this->createMock(CohortRepository::class),
        );
    }

    public function testLearnerCannotJoinWithoutOpenTicket(): void
    {
        $session = new LiveSession(1, 2, 10, 'Unit 1 Live', '2026-07-16 10:00:00', '2026-07-16 12:00:00', 'active', 'room-1', 'now');
        $module = new Module(10, 5, 'Unit 1', 1, 'now');
        $course = new Course(5, 'Course', null, 'published', 'now', 'now');
        $cohort = new Cohort(2, 5, 'Cohort A', '2026-01-01', '2026-06-30', 'now', 'now');
        $ticket = new ReadinessTicket(7, 2, 10, 'locked', null, null, null);

        $sessionRepo = $this->createMock(LiveSessionRepository::class);
        $sessionRepo->method('findById')->willReturn($session);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('findById')->willReturn($module);

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findById')->willReturn($cohort);
        $cohortRepo->method('findActiveEnrollmentForUser')->willReturn($cohort);

        $ticketRepo = $this->createMock(ReadinessTicketRepository::class);
        $ticketRepo->method('find')->willReturn($ticket);

        $service = $this->createService($sessionRepo, null, $ticketRepo, $moduleRepo, $courseRepo, $cohortRepo);
        $context = $service->getRoomContext(1, 7, ['learner']);

        $this->assertNotNull($context);
        $this->assertFalse($context['can_join']);
    }

    public function testHostCanJoinWithoutTicket(): void
    {
        $session = new LiveSession(1, 2, 10, 'Unit 1 Live', '2026-07-16 10:00:00', '2026-07-16 12:00:00', 'active', 'room-1', 'now');
        $module = new Module(10, 5, 'Unit 1', 1, 'now');
        $course = new Course(5, 'Course', null, 'published', 'now', 'now');
        $cohort = new Cohort(2, 5, 'Cohort A', '2026-01-01', '2026-06-30', 'now', 'now');

        $sessionRepo = $this->createMock(LiveSessionRepository::class);
        $sessionRepo->method('findById')->willReturn($session);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('findById')->willReturn($module);

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findById')->willReturn($cohort);

        $service = $this->createService($sessionRepo, null, null, $moduleRepo, $courseRepo, $cohortRepo);
        $context = $service->getRoomContext(1, 99, ['instructor']);

        $this->assertNotNull($context);
        $this->assertTrue($context['can_join']);
        $this->assertTrue($context['is_host']);
    }
}
