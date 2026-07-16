<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Module;
use App\Repositories\CohortRepository;
use App\Repositories\DiscussionRepository;
use App\Repositories\ModuleRepository;
use App\Services\AuthorizationService;
use App\Services\DiscussionService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class DiscussionServiceTest extends TestCase
{
    public function testCreatePostValidatesBody(): void
    {
        $module = new Module(1, 10, 'Unit 1', 1, 'now');

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('findById')->willReturn($module);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findActiveEnrollmentForUser')->willReturn(new \App\Models\Cohort(1, 10, 'Cohort', '2026-01-01', '2026-12-31', 'now', 'now'));

        $authz = $this->createMock(AuthorizationService::class);
        $authz->method('hasAnyRole')->willReturn(false);

        $service = new DiscussionService(
            $this->createMock(DiscussionRepository::class),
            $moduleRepo,
            $cohortRepo,
            $authz,
        );

        $this->expectException(ValidationException::class);
        $service->createPost(1, 7, ['body' => '']);
    }
}
