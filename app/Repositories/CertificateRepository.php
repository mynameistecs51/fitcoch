<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Certificate;

class CertificateRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findByHash(string $hash): ?Certificate
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM certificates WHERE verification_hash = :hash LIMIT 1'
        );
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch();

        return $row ? Certificate::fromArray($row) : null;
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?Certificate
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM certificates
             WHERE user_id = :user_id AND course_id = :course_id
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        $row = $stmt->fetch();

        return $row ? Certificate::fromArray($row) : null;
    }

    public function create(int $userId, int $courseId, string $verificationHash): Certificate
    {
        $stmt = $this->db->prepare(
            'INSERT INTO certificates (user_id, course_id, verification_hash)
             VALUES (:user_id, :course_id, :verification_hash)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
            'verification_hash' => $verificationHash,
        ]);

        $created = $this->findByUserAndCourse($userId, $courseId);

        if ($created === null) {
            throw new \RuntimeException('Failed to create certificate.');
        }

        return $created;
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM certificates');
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<int, Certificate>
     */
    public function listByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM certificates WHERE user_id = :user_id ORDER BY awarded_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(
            static fn (array $row): Certificate => Certificate::fromArray($row),
            $stmt->fetchAll(),
        );
    }
}
