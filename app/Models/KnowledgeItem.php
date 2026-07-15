<?php

declare(strict_types=1);

namespace App\Models;

class KnowledgeItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $courseId,
        public readonly string $conceptName,
        public readonly ?string $description,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            courseId: (int) $row['course_id'],
            conceptName: (string) $row['concept_name'],
            description: isset($row['description']) ? (string) $row['description'] : null,
        );
    }
}
