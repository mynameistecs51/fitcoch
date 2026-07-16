<?php

declare(strict_types=1);

namespace App\Models;

class UserStreak
{
    public function __construct(
        public readonly int $userId,
        public readonly int $currentStreak,
        public readonly int $longestStreak,
        public readonly ?string $lastActivityDate,
        public readonly int $shieldsCount,
        public readonly string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            userId: (int) $row['user_id'],
            currentStreak: (int) $row['current_streak'],
            longestStreak: (int) $row['longest_streak'],
            lastActivityDate: isset($row['last_activity_date']) ? (string) $row['last_activity_date'] : null,
            shieldsCount: (int) $row['shields_count'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'current_streak' => $this->currentStreak,
            'longest_streak' => $this->longestStreak,
            'last_activity_date' => $this->lastActivityDate,
            'shields_count' => $this->shieldsCount,
            'updated_at' => $this->updatedAt,
        ];
    }
}
