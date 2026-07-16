<?php

declare(strict_types=1);

namespace App\Models;

class UserBadge
{
    public function __construct(
        public readonly int $userId,
        public readonly int $badgeId,
        public readonly string $awardedAt,
        public readonly ?Badge $badge = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row, ?Badge $badge = null): self
    {
        return new self(
            userId: (int) $row['user_id'],
            badgeId: (int) $row['badge_id'],
            awardedAt: (string) $row['awarded_at'],
            badge: $badge,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = [
            'badge_id' => $this->badgeId,
            'awarded_at' => $this->awardedAt,
        ];

        if ($this->badge !== null) {
            $payload['badge'] = $this->badge->toArray();
        }

        return $payload;
    }
}
