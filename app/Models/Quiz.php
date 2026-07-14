<?php

declare(strict_types=1);

namespace App\Models;

class Quiz
{
    public function __construct(
        public readonly int $id,
        public readonly int $moduleId,
        public readonly string $quizType,
        public readonly string $title,
        public readonly int $passingScorePct,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            moduleId: (int) $row['module_id'],
            quizType: (string) $row['quiz_type'],
            title: (string) $row['title'],
            passingScorePct: (int) $row['passing_score_pct'],
            createdAt: (string) $row['created_at'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->moduleId,
            'quiz_type' => $this->quizType,
            'title' => $this->title,
            'passing_score_pct' => $this->passingScorePct,
            'created_at' => $this->createdAt,
        ];
    }
}
