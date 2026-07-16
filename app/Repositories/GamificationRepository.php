<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\UserStreak;

class GamificationRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findStreak(int $userId): ?UserStreak
    {
        $stmt = $this->db->prepare('SELECT * FROM user_streaks WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return $row ? UserStreak::fromArray($row) : null;
    }

    public function ensureStreak(int $userId): UserStreak
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user_streaks (user_id) VALUES (:user_id)
             ON DUPLICATE KEY UPDATE user_id = user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        $streak = $this->findStreak($userId);

        if ($streak === null) {
            throw new \RuntimeException('Failed to initialize user streak.');
        }

        return $streak;
    }

    /**
     * @param array{
     *     current_streak?: int,
     *     longest_streak?: int,
     *     last_activity_date?: ?string
     * } $data
     */
    public function updateStreak(int $userId, array $data): UserStreak
    {
        $this->ensureStreak($userId);

        $stmt = $this->db->prepare(
            'UPDATE user_streaks
             SET current_streak = :current_streak,
                 longest_streak = :longest_streak,
                 last_activity_date = :last_activity_date
             WHERE user_id = :user_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'current_streak' => $data['current_streak'] ?? 0,
            'longest_streak' => $data['longest_streak'] ?? 0,
            'last_activity_date' => $data['last_activity_date'] ?? null,
        ]);

        $streak = $this->findStreak($userId);

        if ($streak === null) {
            throw new \RuntimeException('Failed to update user streak.');
        }

        return $streak;
    }

    public function insertXpTransaction(int $userId, int $xpAmount, string $activityType): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO xp_transactions (user_id, xp_amount, activity_type)
             VALUES (:user_id, :xp_amount, :activity_type)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'xp_amount' => $xpAmount,
            'activity_type' => $activityType,
        ]);
    }

    public function sumXpForUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(xp_amount), 0) AS total_xp
             FROM xp_transactions
             WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) ($stmt->fetch()['total_xp'] ?? 0);
    }

    public function countXpByActivityType(int $userId, string $activityType): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total
             FROM xp_transactions
             WHERE user_id = :user_id AND activity_type = :activity_type'
        );
        $stmt->execute([
            'user_id' => $userId,
            'activity_type' => $activityType,
        ]);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function findBadgeByName(string $name): ?Badge
    {
        $stmt = $this->db->prepare('SELECT * FROM badges WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        return $row ? Badge::fromArray($row) : null;
    }

    /** @return array<int, UserBadge> */
    public function listUserBadges(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ub.user_id, ub.badge_id, ub.awarded_at, b.id, b.name, b.description, b.icon_url
             FROM user_badges ub
             INNER JOIN badges b ON b.id = ub.badge_id
             WHERE ub.user_id = :user_id
             ORDER BY ub.awarded_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(static function (array $row): UserBadge {
            $badge = Badge::fromArray($row);

            return UserBadge::fromArray($row, $badge);
        }, $stmt->fetchAll());
    }

    public function hasUserBadge(int $userId, int $badgeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM user_badges WHERE user_id = :user_id AND badge_id = :badge_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'badge_id' => $badgeId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function awardBadge(int $userId, int $badgeId): bool
    {
        if ($this->hasUserBadge($userId, $badgeId)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO user_badges (user_id, badge_id) VALUES (:user_id, :badge_id)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'badge_id' => $badgeId,
        ]);

        return true;
    }
}
