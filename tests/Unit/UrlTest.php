<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testBuildsUrlWithBasePath(): void
    {
        Url::setBasePath('/fitcoch/public');

        $this->assertSame('/fitcoch/public/', Url::to('/'));
        $this->assertSame('/fitcoch/public/login', Url::to('/login'));
        $this->assertSame('/fitcoch/public/lang/th', Url::to('/lang/th'));
    }

    public function testBuildsUrlWithoutBasePath(): void
    {
        Url::setBasePath('');

        $this->assertSame('/', Url::to('/'));
        $this->assertSame('/login', Url::to('/login'));
    }
}
