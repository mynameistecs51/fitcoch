<?php

declare(strict_types=1);

namespace App\Models;

class LiveSession
{
    public function __construct(
        public readonly int $id,
        public readonly int $cohortId,
        public readonly int $moduleId,
        public readonly string $title,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly string $status,
        public readonly string $roomId,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            cohortId: (int) $row['cohort_id'],
            moduleId: (int) $row['module_id'],
            title: (string) $row['title'],
            startTime: (string) $row['start_time'],
            endTime: (string) $row['end_time'],
            status: (string) $row['status'],
            roomId: (string) $row['room_id'],
            createdAt: (string) $row['created_at'],
        );
    }

    public function isJoinable(): bool
    {
        return in_array($this->status, ['scheduled', 'active'], true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cohort_id' => $this->cohortId,
            'module_id' => $this->moduleId,
            'title' => $this->title,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'status' => $this->status,
            'room_id' => $this->roomId,
            'created_at' => $this->createdAt,
        ];
    }
}
