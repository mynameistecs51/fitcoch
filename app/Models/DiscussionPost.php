<?php

declare(strict_types=1);

namespace App\Models;

class DiscussionPost
{
    public function __construct(
        public readonly int $id,
        public readonly int $moduleId,
        public readonly int $userId,
        public readonly string $body,
        public readonly string $createdAt,
        public readonly string $authorName = '',
        public readonly string $authorEmail = '',
        public readonly bool $isResponder = false,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->moduleId,
            'user_id' => $this->userId,
            'body' => $this->body,
            'created_at' => $this->createdAt,
            'author_name' => $this->authorName,
            'is_responder' => $this->isResponder,
        ];
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        $firstName = trim((string) ($row['first_name'] ?? ''));
        $lastName = trim((string) ($row['last_name'] ?? ''));
        $authorName = trim($firstName . ' ' . $lastName);

        return new self(
            id: (int) $row['id'],
            moduleId: (int) $row['module_id'],
            userId: (int) $row['user_id'],
            body: (string) $row['body'],
            createdAt: (string) $row['created_at'],
            authorName: $authorName !== '' ? $authorName : (string) ($row['email'] ?? ''),
            authorEmail: (string) ($row['email'] ?? ''),
            isResponder: (bool) ($row['is_responder'] ?? false),
        );
    }
}
