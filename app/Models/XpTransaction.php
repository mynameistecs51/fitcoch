<?php

declare(strict_types=1);

namespace App\Models;

class XpTransaction
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $xpAmount,
        public readonly string $activityType,
        public readonly string $earnedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            xpAmount: (int) $row['xp_amount'],
            activityType: (string) $row['activity_type'],
            earnedAt: (string) $row['earned_at'],
        );
    }
}
