<?php

declare(strict_types=1);

namespace App\Models;

class Certificate
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $courseId,
        public readonly string $verificationHash,
        public readonly string $awardedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            courseId: (int) $row['course_id'],
            verificationHash: (string) $row['verification_hash'],
            awardedAt: (string) $row['awarded_at'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'course_id' => $this->courseId,
            'verification_hash' => $this->verificationHash,
            'awarded_at' => $this->awardedAt,
        ];
    }
}
