<?php

declare(strict_types=1);

namespace App\Models;

class SpacedRepSchedule
{
    public function __construct(
        public readonly int $userId,
        public readonly int $knowledgeItemId,
        public readonly int $intervalDays,
        public readonly float $easinessFactor,
        public readonly int $repetitionNumber,
        public readonly string $nextReviewDate,
        public readonly ?string $lastReviewedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            userId: (int) $row['user_id'],
            knowledgeItemId: (int) $row['knowledge_item_id'],
            intervalDays: (int) $row['interval_days'],
            easinessFactor: (float) $row['easiness_factor'],
            repetitionNumber: (int) $row['repetition_number'],
            nextReviewDate: (string) $row['next_review_date'],
            lastReviewedAt: isset($row['last_reviewed_at']) ? (string) $row['last_reviewed_at'] : null,
        );
    }
}
