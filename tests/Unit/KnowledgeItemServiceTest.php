<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use App\Repositories\CourseRepository;
use App\Repositories\KnowledgeItemRepository;
use App\Repositories\ModuleRepository;
use App\Services\KnowledgeItemService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class KnowledgeItemServiceTest extends TestCase
{
    public function testUpdateItemValidatesConceptName(): void
    {
        $course = new Course(1, 'Course A', null, 'published', 'now', 'now');
        $item = new \App\Models\KnowledgeItem(5, 1, 'Concept A', 'Desc');

        $courseRepo = $this->createMock(CourseRepository::class);
        $itemRepo = $this->createMock(KnowledgeItemRepository::class);
        $itemRepo->method('findById')->willReturn($item);

        $service = new KnowledgeItemService(
            $courseRepo,
            $this->createMock(ModuleRepository::class),
            $itemRepo,
        );

        $this->expectException(ValidationException::class);
        $service->updateItem(1, 5, ['concept_name' => '', 'description' => 'x']);
    }

    public function testSyncFromModulesCreatesMissingItems(): void
    {
        $course = new Course(1, 'Course A', null, 'published', 'now', 'now');
        $module = new Module(10, 1, 'Unit 1', 1, 'now');

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('listByCourseId')->willReturn([$module]);

        $itemRepo = $this->createMock(KnowledgeItemRepository::class);
        $itemRepo->method('existsByCourseAndConcept')->willReturn(false);
        $itemRepo->expects($this->once())->method('create');

        $service = new KnowledgeItemService($courseRepo, $moduleRepo, $itemRepo);
        $created = $service->syncFromModules(1);

        $this->assertSame(1, $created);
    }
}
