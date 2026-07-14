<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
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
        ?VideoService $videoService = null,
    ): CourseService {
        return new CourseService(
            $courseRepo ?? $this->createMock(CourseRepository::class),
            $moduleRepo ?? $this->createMock(ModuleRepository::class),
            $nuggetRepo ?? $this->createMock(NuggetRepository::class),
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
}
