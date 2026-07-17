<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $studentId,
        public readonly string $titlePrefix,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $status,
        public readonly string $timezone,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            studentId: isset($row['student_id']) && $row['student_id'] !== null && $row['student_id'] !== ''
                ? (string) $row['student_id']
                : null,
            titlePrefix: (string) ($row['title_prefix'] ?? ''),
            email: (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            firstName: (string) $row['first_name'],
            lastName: (string) $row['last_name'],
            status: (string) $row['status'],
            timezone: (string) $row['timezone'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    public function fullName(): string
    {
        $parts = array_filter([
            trim($this->titlePrefix),
            trim($this->firstName),
            trim($this->lastName),
        ], static fn (string $part): bool => $part !== '');

        return implode(' ', $parts);
    }

    /** @return array<string, mixed> */
    public function toPublicArray(array $roles = []): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->studentId,
            'title_prefix' => $this->titlePrefix,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->fullName(),
            'timezone' => $this->timezone,
            'roles' => $roles,
        ];
    }
}
