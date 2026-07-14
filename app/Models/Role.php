<?php

declare(strict_types=1);

namespace App\Models;

class Role
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            name: (string) $row['name'],
            description: isset($row['description']) ? (string) $row['description'] : null,
        );
    }
}
