<?php

declare(strict_types=1);

namespace App\Models;

class Question
{
    /**
     * @param array<int, array{option_number: int, option_text: string, is_correct?: bool}> $options
     */
    public function __construct(
        public readonly int $id,
        public readonly int $quizId,
        public readonly string $questionText,
        public readonly string $questionType,
        public readonly int $points,
        public readonly array $options = [],
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            quizId: (int) $row['quiz_id'],
            questionText: (string) $row['question_text'],
            questionType: (string) $row['question_type'],
            points: (int) $row['points'],
        );
    }

    /** @return array<string, mixed> */
    public function toLearnerArray(): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->questionText,
            'question_type' => $this->questionType,
            'points' => $this->points,
            'options' => array_map(static fn (array $option): array => [
                'option_number' => $option['option_number'],
                'option_text' => $option['option_text'],
            ], $this->options),
        ];
    }
}
