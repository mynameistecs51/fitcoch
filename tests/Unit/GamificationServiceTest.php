<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserStreak;
use App\Repositories\GamificationRepository;
use App\Repositories\UserRepository;
use App\Services\GamificationService;
use PHPUnit\Framework\TestCase;

class GamificationServiceTest extends TestCase
{
    private function createService(
        ?GamificationRepository $repo = null,
        ?UserRepository $userRepo = null,
    ): GamificationService {
        return new GamificationService(
            $repo ?? $this->createMock(GamificationRepository::class),
            $userRepo ?? $this->createMock(UserRepository::class),
        );
    }

    private function sampleUser(): User
    {
        return new User(
            1,
            'learner@test.com',
            'hash',
            'Test',
            'User',
            'active',
            'Asia/Bangkok',
            'now',
            'now',
        );
    }

    public function testUpdateStreakIncrementsWhenLastActivityWasYesterday(): void
    {
        $user = $this->sampleUser();
        $today = (new GamificationService(
            $this->createMock(GamificationRepository::class),
            $this->createMock(UserRepository::class),
        ))->todayForTimezone($user->timezone);

        $yesterday = (new \DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d');
        $existing = new UserStreak(1, 3, 5, $yesterday, 0, 'now');

        $repo = $this->createMock(GamificationRepository::class);
        $repo->method('ensureStreak')->willReturn($existing);
        $repo->expects($this->once())->method('updateStreak')->with(
            1,
            $this->callback(static function (array $data): bool {
                return $data['current_streak'] === 4 && $data['longest_streak'] === 5;
            }),
        )->willReturn(new UserStreak(1, 4, 5, $today, 0, 'now'));

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findById')->willReturn($user);

        $service = $this->createService($repo, $userRepo);
        $streak = $service->updateStreakForActivity(1);

        $this->assertSame(4, $streak->currentStreak);
    }

    public function testUpdateStreakStartsAtOneForFirstActivity(): void
    {
        $user = $this->sampleUser();
        $existing = new UserStreak(1, 0, 0, null, 0, 'now');

        $repo = $this->createMock(GamificationRepository::class);
        $repo->method('ensureStreak')->willReturn($existing);
        $repo->expects($this->once())->method('updateStreak')->with(
            1,
            $this->callback(static fn (array $data): bool => $data['current_streak'] === 1),
        )->willReturn(new UserStreak(1, 1, 1, '2026-07-16', 0, 'now'));

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findById')->willReturn($user);

        $service = $this->createService($repo, $userRepo);
        $streak = $service->updateStreakForActivity(1);

        $this->assertSame(1, $streak->currentStreak);
    }

    public function testUpdateStreakDoesNotIncrementTwiceOnSameDay(): void
    {
        $user = $this->sampleUser();
        $today = (new GamificationService(
            $this->createMock(GamificationRepository::class),
            $this->createMock(UserRepository::class),
        ))->todayForTimezone($user->timezone);
        $existing = new UserStreak(1, 2, 2, $today, 0, 'now');

        $repo = $this->createMock(GamificationRepository::class);
        $repo->method('ensureStreak')->willReturn($existing);
        $repo->expects($this->never())->method('updateStreak');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findById')->willReturn($user);

        $service = $this->createService($repo, $userRepo);
        $streak = $service->updateStreakForActivity(1);

        $this->assertSame(2, $streak->currentStreak);
    }

    public function testRecordQuizPassedAwardsReadinessXpWithoutUpdatingStreak(): void
    {
        $streak = new UserStreak(1, 2, 2, '2026-07-16', 0, 'now');

        $repo = $this->createMock(GamificationRepository::class);
        $repo->expects($this->once())->method('insertXpTransaction')->with(1, 150, GamificationService::ACTIVITY_QUIZ_PASSED);
        $repo->method('ensureStreak')->willReturn($streak);
        $repo->method('countXpByActivityType')->willReturn(1);
        $repo->method('findBadgeByName')->willReturn(null);

        $service = $this->createService($repo);
        $result = $service->recordQuizPassed(1, 85, 'readiness');

        $this->assertSame(150, $result['xp_awarded']);
        $this->assertSame(2, $result['current_streak']);
    }
}
