<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LocaleService;
use PHPUnit\Framework\TestCase;

class LocaleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        LocaleService::set('th');
    }

    public function testAlwaysUsesThaiLocale(): void
    {
        LocaleService::set('en');
        $this->assertSame('th', LocaleService::get());
        $this->assertSame('เข้าสู่ระบบ', LocaleService::translate('auth.sign_in'));
    }

    public function testTranslatesThaiString(): void
    {
        $this->assertSame('เข้าสู่ระบบ', LocaleService::translate('auth.sign_in'));
    }

    public function testReplacesPlaceholders(): void
    {
        $this->assertSame(
            'สวัสดี, John!',
            LocaleService::translate('dashboard.welcome', ['name' => 'John'])
        );
    }

    public function testTranslatesRoleNames(): void
    {
        $roles = LocaleService::translateRoles(['learner', 'admin']);

        $this->assertSame('ผู้เรียน', $roles[0]);
        $this->assertSame('ผู้ดูแลระบบ', $roles[1]);
    }
}
