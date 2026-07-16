<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Module;
use App\Repositories\AnalyticsRepository;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\ReadinessTicketRepository;
use App\Services\AnalyticsService;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    public function testComputeCohortReadinessMetricsTriggersAlertBelowSixtyPercent(): void
    {
        $analyticsRepo = $this->createMock(AnalyticsRepository::class);
        $analyticsRepo->method('getTotalEnrolledCount')->willReturn(10);
        $analyticsRepo->method('getCompletedPrepCount')->willReturn(4);

        $service = new AnalyticsService(
            $analyticsRepo,
            $this->createMock(CohortRepository::class),
            $this->createMock(CourseRepository::class),
            $this->createMock(ModuleRepository::class),
            $this->createMock(QuizRepository::class),
            $this->createMock(ReadinessTicketRepository::class),
            $this->createMock(QuizAttemptRepository::class),
        );

        $metrics = $service->computeCohortReadinessMetrics(1, 10);

        $this->assertSame(10, $metrics['total_enrolled']);
        $this->assertSame(4, $metrics['completed_prep']);
        $this->assertSame(40, $metrics['readiness_pct']);
        $this->assertTrue($metrics['alert_triggered']);
    }

    public function testComputeCohortReadinessMetricsDoesNotTriggerAlertAtThreshold(): void
    {
        $analyticsRepo = $this->createMock(AnalyticsRepository::class);
        $analyticsRepo->method('getTotalEnrolledCount')->willReturn(10);
        $analyticsRepo->method('getCompletedPrepCount')->willReturn(6);

        $service = new AnalyticsService(
            $analyticsRepo,
            $this->createMock(CohortRepository::class),
            $this->createMock(CourseRepository::class),
            $this->createMock(ModuleRepository::class),
            $this->createMock(QuizRepository::class),
            $this->createMock(ReadinessTicketRepository::class),
            $this->createMock(QuizAttemptRepository::class),
        );

        $metrics = $service->computeCohortReadinessMetrics(1, 10);

        $this->assertSame(60, $metrics['readiness_pct']);
        $this->assertFalse($metrics['alert_triggered']);
    }

    public function testBuildCohortAnalyticsReturnsNullWhenCohortMissing(): void
    {
        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findById')->willReturn(null);

        $service = new AnalyticsService(
            $this->createMock(AnalyticsRepository::class),
            $cohortRepo,
            $this->createMock(CourseRepository::class),
            $this->createMock(ModuleRepository::class),
            $this->createMock(QuizRepository::class),
            $this->createMock(ReadinessTicketRepository::class),
            $this->createMock(QuizAttemptRepository::class),
        );

        $this->assertNull($service->buildCohortAnalytics(99));
    }

    public function testBuildCohortAnalyticsUsesRequestedModule(): void
    {
        $cohort = new Cohort(1, 5, 'Cohort A', '2026-01-01', '2026-12-31', 'now', 'now');
        $course = new Course(5, 'Course', null, 'published', 'now', 'now');
        $moduleA = new Module(10, 5, 'Unit 1', 1, 'now');
        $moduleB = new Module(11, 5, 'Unit 2', 2, 'now');

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findById')->willReturn($cohort);

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('listByCourseId')->willReturn([$moduleA, $moduleB]);

        $analyticsRepo = $this->createMock(AnalyticsRepository::class);
        $analyticsRepo->method('getTotalEnrolledCount')->willReturn(3);
        $analyticsRepo->method('getCompletedPrepCount')->willReturn(2);
        $analyticsRepo->method('findTopMisconceptions')->willReturn([]);

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('listByModuleIds')->willReturn([]);
        $quizRepo->method('findReadinessByModuleId')->willReturn(null);

        $ticketRepo = $this->createMock(ReadinessTicketRepository::class);
        $ticketRepo->method('listEnrollmentStatuses')->willReturn([]);

        $service = new AnalyticsService(
            $analyticsRepo,
            $cohortRepo,
            $courseRepo,
            $moduleRepo,
            $quizRepo,
            $ticketRepo,
            $this->createMock(QuizAttemptRepository::class),
        );

        $panel = $service->buildCohortAnalytics(1, 11);

        $this->assertNotNull($panel);
        $this->assertSame(11, $panel['selected_module']->id);
        $this->assertSame(2, $panel['metrics']['completed_prep']);
    }
}
