<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\AdminService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class AdminServiceTest extends TestCase
{
    public function testCannotRemoveOwnAdminRole(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $roleRepo = $this->createMock(RoleRepository::class);

        $userRepo->method('findById')->willReturn(
            new \App\Models\User(1, 'admin@test.com', 'hash', 'Admin', 'User', 'active', 'UTC', 'now', 'now')
        );

        $service = new AdminService($userRepo, $roleRepo);

        $this->expectException(ValidationException::class);
        $service->updateUserRoles(1, 1, ['learner']);
    }

    public function testCannotSuspendSelf(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $roleRepo = $this->createMock(RoleRepository::class);

        $userRepo->method('findById')->willReturn(
            new \App\Models\User(1, 'admin@test.com', 'hash', 'Admin', 'User', 'active', 'UTC', 'now', 'now')
        );

        $service = new AdminService($userRepo, $roleRepo);

        $this->expectException(ValidationException::class);
        $service->updateUserStatus(1, 1, 'suspended');
    }
}
