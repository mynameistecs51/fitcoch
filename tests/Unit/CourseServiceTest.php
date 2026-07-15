<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Cohort;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\NuggetRepository;
use App\Services\CourseService;
use App\Services\ValidationException;
use App\Services\VideoService;
use PHPUnit\Framework\TestCase;

class CourseServiceTest extends TestCase
{
    private function createService(
        ?CourseRepository $courseRepo = null,
        ?ModuleRepository $moduleRepo = null,
        ?NuggetRepository $nuggetRepo = null,
        ?CohortRepository $cohortRepo = null,
        ?VideoService $videoService = null,
    ): CourseService {
        return new CourseService(
            $courseRepo ?? $this->createMock(CourseRepository::class),
            $moduleRepo ?? $this->createMock(ModuleRepository::class),
            $nuggetRepo ?? $this->createMock(NuggetRepository::class),
            $cohortRepo ?? $this->createMock(CohortRepository::class),
            $videoService ?? new VideoService(),
        );
    }

    public function testCreateCourseRequiresTitle(): void
    {
        $service = $this->createService();

        $this->expectException(ValidationException::class);
        $service->createCourse(['title' => '', 'status' => 'draft']);
    }

    public function testCreateCourseRejectsInvalidStatus(): void
    {
        $service = $this->createService();

        $this->expectException(ValidationException::class);
        $service->createCourse(['title' => 'Test Course', 'status' => 'invalid']);
    }

    public function testUpdateCourseReturnsUpdatedCourse(): void
    {
        $course = new Course(1, 'Old', null, 'draft', 'now', 'now');
        $updated = new Course(1, 'New Title', 'Desc', 'published', 'now', 'now');

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturnOnConsecutiveCalls($course, $updated);
        $courseRepo->expects($this->once())->method('update')->willReturn($updated);

        $service = $this->createService($courseRepo);
        $result = $service->updateCourse(1, [
            'title' => 'New Title',
            'description' => 'Desc',
            'status' => 'published',
        ]);

        $this->assertSame('New Title', $result->title);
        $this->assertSame('published', $result->status);
    }

    public function testCreateCourseRejectsInvalidYoutubeUrl(): void
    {
        $course = new Course(1, 'Test', null, 'draft', 'now', 'now');
        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('create')->willReturn($course);

        $service = $this->createService($courseRepo);

        $this->expectException(ValidationException::class);
        $service->createCourse([
            'title' => 'Test Course',
            'status' => 'draft',
            'video_source' => 'youtube',
            'youtube_url' => 'not-a-valid-url',
        ]);
    }

    public function testEnrollLearnerCreatesCohortWhenMissing(): void
    {
        $course = new Course(2, 'Open Course', 'Desc', 'published', 'now', 'now');
        $cohort = new Cohort(5, 2, 'Open cohort', '2026-01-01', '2026-12-31', 'now', 'now');

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);
        $courseRepo->method('isUserEnrolled')->willReturn(false);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('listByCourseId')->willReturn([]);
        $cohortRepo->expects($this->once())->method('create')->willReturn($cohort);
        $cohortRepo->expects($this->once())->method('enrollUser')->with(5, 9);

        $service = $this->createService($courseRepo, null, null, $cohortRepo);
        $service->enrollLearner(9, 2);
    }
}
