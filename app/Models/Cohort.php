<?php

declare(strict_types=1);

namespace App\Models;

class Cohort
{
    public function __construct(
        public readonly int $id,
        public readonly int $courseId,
        public readonly string $name,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            courseId: (int) $row['course_id'],
            name: (string) $row['name'],
            startDate: (string) $row['start_date'],
            endDate: (string) $row['end_date'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }
}
