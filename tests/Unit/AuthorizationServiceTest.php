<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\RoleRepository;
use App\Services\AuthorizationService;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    public function testHasRoleReturnsTrueWhenUserHasRole(): void
    {
        $roleRepo = $this->createMock(RoleRepository::class);
        $roleRepo->method('getRoleNamesForUser')->willReturn(['learner', 'instructor']);

        $service = new AuthorizationService($roleRepo);

        $this->assertTrue($service->hasRole(1, 'learner'));
        $this->assertTrue($service->hasRole(1, 'instructor'));
        $this->assertFalse($service->hasRole(1, 'admin'));
    }

    public function testHasAnyRoleChecksMultipleRoles(): void
    {
        $roleRepo = $this->createMock(RoleRepository::class);
        $roleRepo->method('getRoleNamesForUser')->willReturn(['learner']);

        $service = new AuthorizationService($roleRepo);

        $this->assertTrue($service->hasAnyRole(1, ['instructor', 'learner']));
        $this->assertFalse($service->hasAnyRole(1, ['instructor', 'admin']));
    }
}
