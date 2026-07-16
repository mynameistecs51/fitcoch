<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserStreak;
use App\Repositories\GamificationRepository;
use App\Repositories\UserRepository;
use DateTime;
use DateTimeZone;
use Exception;

class GamificationService
{
    public const ACTIVITY_NUGGET_COMPLETED = 'NUGGET_COMPLETED';
    public const ACTIVITY_SPACED_REP = 'SPACED_REP_QUIZ_SUBMITTED';
    public const ACTIVITY_QUIZ_PASSED = 'QUIZ_PASSED';
    public const ACTIVITY_QUIZ_PERFECT = 'quiz_perfect';

    private const XP_NUGGET = 10;
    private const XP_SPACED_REP = 50;
    private const XP_QUIZ_READINESS = 150;
    private const XP_QUIZ_PERFECT = 50;
    private const XP_QUIZ_PASS = 25;

    public function __construct(
        private readonly GamificationRepository $repo,
        private readonly UserRepository $userRepo,
    ) {
    }

    /**
     * @return array{xp_awarded: int, current_streak: int, badges_awarded: array<int, string>}
     */
    public function recordNuggetCompleted(int $userId): array
    {
        $this->repo->insertXpTransaction($userId, self::XP_NUGGET, self::ACTIVITY_NUGGET_COMPLETED);
        $streak = $this->updateStreakForActivity($userId);
        $badges = $this->evaluateBadges($userId, $streak);

        return [
            'xp_awarded' => self::XP_NUGGET,
            'current_streak' => $streak->currentStreak,
            'badges_awarded' => $badges,
        ];
    }

    /**
     * @return array{xp_awarded: int, current_streak: int, badges_awarded: array<int, string>}
     */
    public function recordQuizPassed(int $userId, int $scorePct, string $quizType): array
    {
        $xp = $this->resolveQuizXp($scorePct, $quizType);
        $activityType = $scorePct >= 100 ? self::ACTIVITY_QUIZ_PERFECT : self::ACTIVITY_QUIZ_PASSED;

        if ($xp > 0) {
            $this->repo->insertXpTransaction($userId, $xp, $activityType);
        }

        $streak = $this->repo->ensureStreak($userId);
        $badges = $this->evaluateBadges($userId, $streak);

        return [
            'xp_awarded' => $xp,
            'current_streak' => $streak->currentStreak,
            'badges_awarded' => $badges,
        ];
    }

    /**
     * @return array{xp_awarded: int, current_streak: int, badges_awarded: array<int, string>}
     */
    public function recordSpacedRepSubmitted(int $userId): array
    {
        $this->repo->insertXpTransaction($userId, self::XP_SPACED_REP, self::ACTIVITY_SPACED_REP);
        $streak = $this->updateStreakForActivity($userId);
        $badges = $this->evaluateBadges($userId, $streak);

        return [
            'xp_awarded' => self::XP_SPACED_REP,
            'current_streak' => $streak->currentStreak,
            'badges_awarded' => $badges,
        ];
    }

    /**
     * @return array{
     *     current_streak: int,
     *     longest_streak: int,
     *     total_xp: int,
     *     badges: array<int, array<string, mixed>>
     * }
     */
    public function getSummary(int $userId): array
    {
        $streak = $this->repo->findStreak($userId);
        $badges = $this->repo->listUserBadges($userId);

        return [
            'current_streak' => $streak?->currentStreak ?? 0,
            'longest_streak' => $streak?->longestStreak ?? 0,
            'total_xp' => $this->repo->sumXpForUser($userId),
            'badges' => array_map(static fn ($userBadge) => $userBadge->toArray(), $badges),
        ];
    }

    public function todayForTimezone(string $timezone): string
    {
        try {
            $tz = new DateTimeZone($timezone !== '' ? $timezone : 'Asia/Bangkok');
        } catch (Exception) {
            $tz = new DateTimeZone('Asia/Bangkok');
        }

        return (new DateTime('now', $tz))->format('Y-m-d');
    }

    public function updateStreakForActivity(int $userId): UserStreak
    {
        $user = $this->userRepo->findById($userId);

        if ($user === null) {
            throw new Exception(__('errors.unauthorized'));
        }

        $today = $this->todayForTimezone($user->timezone);
        $streak = $this->repo->ensureStreak($userId);
        $lastDate = $streak->lastActivityDate;

        if ($lastDate === $today) {
            return $streak;
        }

        $currentStreak = 1;

        if ($lastDate !== null) {
            $yesterday = $this->shiftDate($today, -1, $user->timezone);

            if ($lastDate === $yesterday) {
                $currentStreak = $streak->currentStreak + 1;
            }
        }

        $longestStreak = max($streak->longestStreak, $currentStreak);

        return $this->repo->updateStreak($userId, [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'last_activity_date' => $today,
        ]);
    }

    private function resolveQuizXp(int $scorePct, string $quizType): int
    {
        if ($scorePct < 0) {
            return 0;
        }

        if ($quizType === 'readiness') {
            return $scorePct >= 80 ? self::XP_QUIZ_READINESS : 0;
        }

        if ($scorePct >= 100) {
            return self::XP_QUIZ_PERFECT;
        }

        return $scorePct >= 80 ? self::XP_QUIZ_PASS : 0;
    }

    /** @return array<int, string> */
    private function evaluateBadges(int $userId, UserStreak $streak): array
    {
        $awarded = [];

        if ($this->repo->countXpByActivityType($userId, self::ACTIVITY_NUGGET_COMPLETED) === 1) {
            $awarded = array_merge($awarded, $this->tryAwardBadge($userId, 'first_nugget'));
        }

        $quizXpCount = $this->repo->countXpByActivityType($userId, self::ACTIVITY_QUIZ_PASSED)
            + $this->repo->countXpByActivityType($userId, self::ACTIVITY_QUIZ_PERFECT);

        if ($quizXpCount === 1) {
            $awarded = array_merge($awarded, $this->tryAwardBadge($userId, 'quiz_passed'));
        }

        if ($streak->currentStreak >= 7) {
            $awarded = array_merge($awarded, $this->tryAwardBadge($userId, 'streak_7'));
        }

        if ($this->repo->countXpByActivityType($userId, self::ACTIVITY_SPACED_REP) >= 10) {
            $awarded = array_merge($awarded, $this->tryAwardBadge($userId, 'review_10'));
        }

        return $awarded;
    }

    /** @return array<int, string> */
    private function tryAwardBadge(int $userId, string $badgeName): array
    {
        $badge = $this->repo->findBadgeByName($badgeName);

        if ($badge === null) {
            return [];
        }

        return $this->repo->awardBadge($userId, $badge->id) ? [$badgeName] : [];
    }

    private function shiftDate(string $date, int $days, string $timezone): string
    {
        try {
            $tz = new DateTimeZone($timezone !== '' ? $timezone : 'Asia/Bangkok');
        } catch (Exception) {
            $tz = new DateTimeZone('Asia/Bangkok');
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date, $tz);

        if ($dt === false) {
            $dt = new DateTime('now', $tz);
        }

        $dt->modify(($days >= 0 ? '+' : '') . $days . ' day');

        return $dt->format('Y-m-d');
    }
}
