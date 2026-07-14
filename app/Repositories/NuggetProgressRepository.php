<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class NuggetProgressRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * @return array{
     *     user_id: int,
     *     nugget_id: int,
     *     progress_percentage: int,
     *     status: string,
     *     completed_at: ?string,
     *     updated_at: string
     * }|null
     */
    public function find(int $userId, int $nuggetId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM nugget_progress WHERE user_id = :user_id AND nugget_id = :nugget_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'nugget_id' => $nuggetId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array{
     *     user_id: int,
     *     nugget_id: int,
     *     progress_percentage: int,
     *     status: string,
     *     completed_at: ?string,
     *     updated_at: string
     * }
     */
    public function upsert(int $userId, int $nuggetId, int $progressPercentage): array
    {
        $progressPercentage = max(0, min(100, $progressPercentage));
        $status = $progressPercentage >= 90 ? 'completed' : 'in_progress';
        $completedAt = $status === 'completed' ? gmdate('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            'INSERT INTO nugget_progress (user_id, nugget_id, progress_percentage, status, completed_at)
             VALUES (:user_id, :nugget_id, :progress_percentage, :status, :completed_at)
             ON DUPLICATE KEY UPDATE
                progress_percentage = GREATEST(progress_percentage, VALUES(progress_percentage)),
                status = IF(GREATEST(progress_percentage, VALUES(progress_percentage)) >= 90, \'completed\', status),
                completed_at = IF(
                    completed_at IS NULL AND GREATEST(progress_percentage, VALUES(progress_percentage)) >= 90,
                    VALUES(completed_at),
                    completed_at
                ),
                updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'user_id' => $userId,
            'nugget_id' => $nuggetId,
            'progress_percentage' => $progressPercentage,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        $record = $this->find($userId, $nuggetId);

        if ($record === null) {
            throw new \RuntimeException('Failed to save nugget progress.');
        }

        return $record;
    }
}
