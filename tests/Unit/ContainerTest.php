<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Container;
use App\Core\Database;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testResolvesAuthServiceWithDependencies(): void
    {
        $container = new Container();
        $container->singleton(Database::class, $this->createMock(Database::class));

        $authService = $container->get(AuthService::class);

        $this->assertInstanceOf(AuthService::class, $authService);
    }

    public function testResolvesNestedRepositoryDependencies(): void
    {
        $container = new Container();
        $container->singleton(Database::class, $this->createMock(Database::class));

        $repository = $container->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    public function testResolvesJwtService(): void
    {
        $container = new Container();

        $jwtService = $container->get(JwtService::class);

        $this->assertInstanceOf(JwtService::class, $jwtService);
    }
}
