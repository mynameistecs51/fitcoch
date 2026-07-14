<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\VideoService;
use PHPUnit\Framework\TestCase;

class VideoServiceTest extends TestCase
{
    public function testNormalizeYoutubeWatchUrl(): void
    {
        $service = new VideoService();

        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            $service->normalizeYoutubeUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        );
    }

    public function testExtractYoutubeIdFromEmbedUrl(): void
    {
        $service = new VideoService();

        $this->assertSame(
            'dQw4w9WgXcQ',
            $service->extractYoutubeId('https://www.youtube.com/embed/dQw4w9WgXcQ')
        );
    }

    public function testNormalizeYoutubeShortUrl(): void
    {
        $service = new VideoService();

        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            $service->normalizeYoutubeUrl('https://youtu.be/dQw4w9WgXcQ')
        );
    }

    public function testNormalizeYoutubeRejectsInvalidUrl(): void
    {
        $service = new VideoService();

        $this->assertNull($service->normalizeYoutubeUrl('https://example.com/video'));
    }
}
