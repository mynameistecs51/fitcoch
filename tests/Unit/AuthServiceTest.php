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
            '6501234567',
            'นาย',
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
        $userRepo->method('findByStudentId')->with('6501234567')->willReturn($user);
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

        $authenticated = $service->authenticate('6501234567', $password);

        $this->assertSame($user, $authenticated);
        $this->assertSame(7, $_SESSION['user_id']);
        $this->assertSame($storedToken, $_SESSION['session_token']);
    }

    public function testAuthenticateWithEmailUsesEmailLookupFirst(): void
    {
        $password = 'Password123!';
        $user = new User(
            3,
            null,
            '',
            'te.chaiwat@gmail.com',
            password_hash($password, PASSWORD_ARGON2ID),
            'Test',
            'User',
            'active',
            'Asia/Bangkok',
            'now',
            'now'
        );

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->expects($this->never())->method('findByStudentId');
        $userRepo->method('findByEmail')->with('te.chaiwat@gmail.com')->willReturn($user);
        $userRepo->method('updateSessionToken');

        $service = new AuthService(
            $userRepo,
            $this->createMock(RoleRepository::class),
            $this->createMock(AuthorizationService::class),
            $this->createMock(JwtService::class),
        );

        $authenticated = $service->authenticate('te.chaiwat@gmail.com', $password);

        $this->assertSame($user, $authenticated);
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
