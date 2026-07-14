<?php

declare(strict_types=1);

namespace App\Models;

class Module
{
    public function __construct(
        public readonly int $id,
        public readonly int $courseId,
        public readonly string $title,
        public readonly int $sequenceOrder,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            courseId: (int) $row['course_id'],
            title: (string) $row['title'],
            sequenceOrder: (int) $row['sequence_order'],
            createdAt: (string) $row['created_at'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->courseId,
            'title' => $this->title,
            'sequence_order' => $this->sequenceOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
