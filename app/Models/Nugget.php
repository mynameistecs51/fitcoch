<?php

declare(strict_types=1);

namespace App\Models;

class Nugget
{
    public function __construct(
        public readonly int $id,
        public readonly int $moduleId,
        public readonly string $title,
        public readonly string $nuggetType,
        public readonly ?string $contentUrl,
        public readonly ?string $contentBody,
        public readonly int $durationSeconds,
        public readonly int $sequenceOrder,
        public readonly string $createdAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            moduleId: (int) $row['module_id'],
            title: (string) $row['title'],
            nuggetType: (string) $row['nugget_type'],
            contentUrl: isset($row['content_url']) ? (string) $row['content_url'] : null,
            contentBody: isset($row['content_body']) ? (string) $row['content_body'] : null,
            durationSeconds: (int) $row['duration_seconds'],
            sequenceOrder: (int) $row['sequence_order'],
            createdAt: (string) $row['created_at'],
        );
    }

    public function isYoutubeVideo(): bool
    {
        if ($this->contentUrl === null || $this->contentUrl === '') {
            return false;
        }

        return str_contains($this->contentUrl, 'youtube.com')
            || str_contains($this->contentUrl, 'youtu.be');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->moduleId,
            'title' => $this->title,
            'nugget_type' => $this->nuggetType,
            'content_url' => $this->contentUrl,
            'content_body' => $this->contentBody,
            'duration_seconds' => $this->durationSeconds,
            'sequence_order' => $this->sequenceOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
