<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Services\CourseService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class CourseServiceTest extends TestCase
{
    public function testCreateCourseRequiresTitle(): void
    {
        $courseRepo = $this->createMock(CourseRepository::class);
        $moduleRepo = $this->createMock(ModuleRepository::class);
        $service = new CourseService($courseRepo, $moduleRepo);

        $this->expectException(ValidationException::class);
        $service->createCourse(['title' => '', 'status' => 'draft']);
    }

    public function testCreateCourseRejectsInvalidStatus(): void
    {
        $courseRepo = $this->createMock(CourseRepository::class);
        $moduleRepo = $this->createMock(ModuleRepository::class);
        $service = new CourseService($courseRepo, $moduleRepo);

        $this->expectException(ValidationException::class);
        $service->createCourse(['title' => 'Test Course', 'status' => 'invalid']);
    }

    public function testUpdateCourseReturnsUpdatedCourse(): void
    {
        $course = new Course(1, 'Old', null, 'draft', 'now', 'now');
        $updated = new Course(1, 'New Title', 'Desc', 'published', 'now', 'now');

        $courseRepo = $this->createMock(CourseRepository::class);
        $moduleRepo = $this->createMock(ModuleRepository::class);

        $courseRepo->method('findById')->willReturnOnConsecutiveCalls($course, $updated);
        $courseRepo->expects($this->once())->method('update')->willReturn($updated);

        $service = new CourseService($courseRepo, $moduleRepo);
        $result = $service->updateCourse(1, [
            'title' => 'New Title',
            'description' => 'Desc',
            'status' => 'published',
        ]);

        $this->assertSame('New Title', $result->title);
        $this->assertSame('published', $result->status);
    }
}
