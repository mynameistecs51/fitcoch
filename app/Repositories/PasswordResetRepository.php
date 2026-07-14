<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PasswordResetRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $this->invalidateActiveForUser($userId);

        $stmt = $this->db->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return array{id: int, user_id: int, token_hash: string, expires_at: string, used_at: ?string}|null
     */
    public function findValidByTokenHash(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE password_reset_tokens SET used_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function invalidateActiveForUser(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE password_reset_tokens
             SET used_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id AND used_at IS NULL'
        );
        $stmt->execute(['user_id' => $userId]);
    }
}
