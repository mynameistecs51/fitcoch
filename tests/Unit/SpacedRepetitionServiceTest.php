<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\SpacedRepetitionService;
use App\Repositories\SpacedRepetitionRepository;
use PHPUnit\Framework\TestCase;

class SpacedRepetitionServiceTest extends TestCase
{
    private function createService(): SpacedRepetitionService
    {
        return new SpacedRepetitionService(
            $this->createMock(SpacedRepetitionRepository::class),
        );
    }

    public function testCalculateSM2CorrectRatingAtRepetitionOneSetsSixDayInterval(): void
    {
        $service = $this->createService();

        $result = $service->calculateSM2(2.5, 1, 1, 4);

        $this->assertSame(6, $result['interval_days']);
        $this->assertSame(2, $result['repetition_number']);
        $this->assertGreaterThanOrEqual(1.3, $result['easiness_factor']);
    }

    public function testCalculateSM2FailureResetsRepetition(): void
    {
        $service = $this->createService();

        $result = $service->calculateSM2(2.5, 3, 15, 1);

        $this->assertSame(1, $result['interval_days']);
        $this->assertSame(0, $result['repetition_number']);
    }

    public function testCalculateSM2FirstSuccessSetsOneDayInterval(): void
    {
        $service = $this->createService();

        $result = $service->calculateSM2(2.5, 0, 1, 3);

        $this->assertSame(1, $result['interval_days']);
        $this->assertSame(1, $result['repetition_number']);
    }

    public function testCalculateSM2ThirdSuccessUsesPreviousIntervalTimesEF(): void
    {
        $service = $this->createService();

        $result = $service->calculateSM2(2.5, 2, 6, 4);

        $this->assertSame(15, $result['interval_days']);
        $this->assertSame(3, $result['repetition_number']);
    }
}
