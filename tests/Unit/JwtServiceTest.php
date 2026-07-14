<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

class JwtServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key-for-jwt-signing-purposes';
        putenv('JWT_SECRET=test-secret-key-for-jwt-signing-purposes');
    }

    public function testEncodesAndDecodesToken(): void
    {
        $service = new JwtService();
        $token = $service->encode(42, 'learner@fitcoch.com');

        $payload = $service->decode($token);

        $this->assertSame(42, $payload['sub']);
        $this->assertSame('learner@fitcoch.com', $payload['email']);
    }

    public function testRevokedTokenIsRejected(): void
    {
        $service = new JwtService();
        $token = $service->encode(42, 'learner@fitcoch.com');
        $payload = $service->decode($token);

        $service->revoke($payload['jti'], $payload['exp']);

        $this->expectException(\Exception::class);
        $service->decode($token);
    }
}
