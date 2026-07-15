<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\AuthorizationService;
use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
    }

    public function testAuthenticateStoresSessionTokenInSessionAndDatabase(): void
    {
        $password = 'Password123!';
        $user = new User(
            7,
            'learner@example.com',
            password_hash($password, PASSWORD_ARGON2ID),
            'Learner',
            'One',
            'active',
            'Asia/Bangkok',
            'now',
            'now'
        );

        $storedToken = null;

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findByEmail')->with('learner@example.com')->willReturn($user);
        $userRepo->expects($this->once())
            ->method('updateSessionToken')
            ->with(7, $this->callback(function (string $token) use (&$storedToken): bool {
                $storedToken = $token;

                return strlen($token) === 64;
            }));

        $service = new AuthService(
            $userRepo,
            $this->createMock(RoleRepository::class),
            $this->createMock(AuthorizationService::class),
            $this->createMock(JwtService::class),
        );

        $authenticated = $service->authenticate('learner@example.com', $password);

        $this->assertSame($user, $authenticated);
        $this->assertSame(7, $_SESSION['user_id']);
        $this->assertSame($storedToken, $_SESSION['session_token']);
    }

    public function testIsActiveWebSessionReturnsFalseWhenSessionTokenMissing(): void
    {
        $_SESSION['user_id'] = 7;

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->never())->method('findSessionToken');

        $service = new AuthService(
            $userRepo,
            $this->createMock(RoleRepository::class),
            $this->createMock(AuthorizationService::class),
            $this->createMock(JwtService::class),
        );

        $this->assertFalse($service->isActiveWebSession(7));
    }

    public function testIsActiveWebSessionReturnsFalseWhenTokenDoesNotMatchDatabase(): void
    {
        $_SESSION['user_id'] = 7;
        $_SESSION['session_token'] = 'browser-token';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findSessionToken')->with(7)->willReturn('other-browser-token');

        $service = new AuthService(
            $userRepo,
            $this->createMock(RoleRepository::class),
            $this->createMock(AuthorizationService::class),
            $this->createMock(JwtService::class),
        );

        $this->assertFalse($service->isActiveWebSession(7));
    }

    public function testIsActiveWebSessionReturnsTrueWhenTokenMatchesDatabase(): void
    {
        $_SESSION['user_id'] = 7;
        $_SESSION['session_token'] = 'active-browser-token';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findSessionToken')->with(7)->willReturn('active-browser-token');

        $service = new AuthService(
            $userRepo,
            $this->createMock(RoleRepository::class),
            $this->createMock(AuthorizationService::class),
            $this->createMock(JwtService::class),
        );

        $this->assertTrue($service->isActiveWebSession(7));
    }
}
