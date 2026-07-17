<?php

declare(strict_types=1);

namespace App\Models;

class Badge
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $iconUrl,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) ($row['id'] ?? $row['badge_id'] ?? 0),
            name: (string) $row['name'],
            description: (string) $row['description'],
            iconUrl: (string) $row['icon_url'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon_url' => $this->iconUrl,
        ];
    }
}
