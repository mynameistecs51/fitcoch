<?php

declare(strict_types=1);

namespace App\Models;

class ReadinessTicket
{
    public function __construct(
        public readonly int $userId,
        public readonly int $cohortId,
        public readonly int $moduleId,
        public readonly string $status,
        public readonly ?int $overriddenBy,
        public readonly ?string $overriddenAt,
        public readonly ?string $unlockedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            userId: (int) $row['user_id'],
            cohortId: (int) $row['cohort_id'],
            moduleId: (int) $row['module_id'],
            status: (string) $row['status'],
            overriddenBy: isset($row['overridden_by']) ? (int) $row['overridden_by'] : null,
            overriddenAt: isset($row['overridden_at']) ? (string) $row['overridden_at'] : null,
            unlockedAt: isset($row['unlocked_at']) ? (string) $row['unlocked_at'] : null,
        );
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['unlocked', 'overridden'], true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'cohort_id' => $this->cohortId,
            'module_id' => $this->moduleId,
            'status' => $this->status,
            'overridden_by' => $this->overriddenBy,
            'overridden_at' => $this->overriddenAt,
            'unlocked_at' => $this->unlockedAt,
        ];
    }
}
