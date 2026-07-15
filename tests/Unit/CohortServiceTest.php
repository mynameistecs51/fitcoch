<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cohort;
use App\Models\Course;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use App\Services\CohortService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class CohortServiceTest extends TestCase
{
    public function testUpdateCohortValidatesName(): void
    {
        $cohort = new Cohort(1, 1, 'Cohort A', '2026-01-01', '2026-12-31', 'now', 'now');

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findById')->with(1)->willReturn($cohort);

        $service = new CohortService(
            $this->createMock(CourseRepository::class),
            $cohortRepo,
            $this->createMock(UserRepository::class),
        );

        $this->expectException(ValidationException::class);
        $service->updateCohort(1, ['name' => '', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31']);
    }

    public function testGetCourseCohortsPanelReturnsNullWhenCourseMissing(): void
    {
        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn(null);

        $service = new CohortService(
            $courseRepo,
            $this->createMock(CohortRepository::class),
            $this->createMock(UserRepository::class),
        );

        $this->assertNull($service->getCourseCohortsPanel(99));
    }

    public function testGetCourseCohortsPanelReturnsCourseAndCohorts(): void
    {
        $course = new Course(1, 'Course A', null, 'published', 'now', 'now');

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('listByCourseId')->willReturn([]);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('listWithRoles')->willReturn([]);

        $service = new CohortService($courseRepo, $cohortRepo, $userRepo);
        $panel = $service->getCourseCohortsPanel(1);

        $this->assertNotNull($panel);
        $this->assertSame('Course A', $panel['course']->title);
        $this->assertSame([], $panel['cohorts']);
    }
}
