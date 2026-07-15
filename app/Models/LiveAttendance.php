<?php

declare(strict_types=1);

namespace App\Models;

class LiveAttendance
{
    public function __construct(
        public readonly int $liveSessionId,
        public readonly int $userId,
        public readonly string $joinedAt,
        public readonly ?string $leftAt,
        public readonly int $totalSeconds,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            liveSessionId: (int) $row['live_session_id'],
            userId: (int) $row['user_id'],
            joinedAt: (string) $row['joined_at'],
            leftAt: isset($row['left_at']) ? (string) $row['left_at'] : null,
            totalSeconds: (int) $row['total_seconds'],
        );
    }

    public function isActive(): bool
    {
        return $this->leftAt === null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'live_session_id' => $this->liveSessionId,
            'user_id' => $this->userId,
            'joined_at' => $this->joinedAt,
            'left_at' => $this->leftAt,
            'total_seconds' => $this->totalSeconds,
        ];
    }
}
