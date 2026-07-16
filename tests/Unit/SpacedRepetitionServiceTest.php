<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GamificationService;
use App\Services\SpacedRepetitionService;
use App\Repositories\SpacedRepetitionRepository;
use PHPUnit\Framework\TestCase;

class SpacedRepetitionServiceTest extends TestCase
{
    private function createService(): SpacedRepetitionService
    {
        return new SpacedRepetitionService(
            $this->createMock(SpacedRepetitionRepository::class),
            $this->createMock(GamificationService::class),
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

    public function testGetDashboardPanelAggregatesCounts(): void
    {
        $user = new \App\Models\User(
            7,
            'learner@test.com',
            'hash',
            'Test',
            'User',
            'active',
            'Asia/Bangkok',
            'now',
            'now',
        );

        $repo = $this->createMock(SpacedRepetitionRepository::class);
        $repo->expects($this->once())->method('ensureSchedulesForUser')->with(7, $this->isType('string'));
        $repo->method('listDueForUser')->willReturn([
            [
                'item' => new \App\Models\KnowledgeItem(1, 10, 'Concept A', 'Desc'),
                'schedule' => new \App\Models\SpacedRepSchedule(7, 1, 1, 2.5, 0, '2026-07-16', null),
                'course_title' => 'Course A',
            ],
        ]);
        $repo->method('countTotalForUser')->willReturn(5);
        $repo->method('countReviewedOnDate')->willReturn(2);
        $repo->method('listUpcomingForUser')->willReturn([]);
        $repo->method('listRecentlyReviewedForUser')->willReturn([]);

        $service = new SpacedRepetitionService(
            $repo,
            $this->createMock(GamificationService::class),
        );
        $panel = $service->getDashboardPanel($user);

        $this->assertSame(1, $panel['due_today']);
        $this->assertSame(5, $panel['total_concepts']);
        $this->assertSame(2, $panel['reviewed_today']);
        $this->assertCount(1, $panel['due_items']);
        $this->assertSame('Concept A', $panel['due_items'][0]['concept_name']);
    }
}
