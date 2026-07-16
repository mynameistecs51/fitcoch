<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\DiscussionPost;

class DiscussionRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, DiscussionPost> */
    public function listByModuleId(int $moduleId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, u.first_name, u.last_name, u.email,
                    EXISTS (
                        SELECT 1
                        FROM user_roles ur
                        INNER JOIN roles r ON r.id = ur.role_id
                        WHERE ur.user_id = d.user_id
                          AND r.name IN (\'instructor\', \'admin\')
                    ) AS is_responder
             FROM module_discussions d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.module_id = :module_id
             ORDER BY d.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('module_id', $moduleId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = array_reverse($stmt->fetchAll());

        return array_map(
            static fn (array $row): DiscussionPost => DiscussionPost::fromArray($row),
            $rows
        );
    }

    /** @return array{module_id: int, user_id: int, body: string} */
    public function create(array $data): DiscussionPost
    {
        $stmt = $this->db->prepare(
            'INSERT INTO module_discussions (module_id, user_id, body)
             VALUES (:module_id, :user_id, :body)'
        );
        $stmt->execute($data);

        $id = (int) $this->db->lastInsertId();
        $stmt = $this->db->prepare(
            'SELECT d.*, u.first_name, u.last_name, u.email,
                    EXISTS (
                        SELECT 1
                        FROM user_roles ur
                        INNER JOIN roles r ON r.id = ur.role_id
                        WHERE ur.user_id = d.user_id
                          AND r.name IN (\'instructor\', \'admin\')
                    ) AS is_responder
             FROM module_discussions d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Failed to create discussion post.');
        }

        return DiscussionPost::fromArray($row);
    }

    /** @param array<int, int> $courseIds */
    public function countUnreadLearnerPostsByCourseIds(int $userId, array $courseIds): array
    {
        if ($courseIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $sql = 'SELECT m.course_id, COUNT(d.id) AS unread_count
                FROM modules m
                INNER JOIN module_discussions d ON d.module_id = m.id
                LEFT JOIN discussion_reads r ON r.module_id = m.id AND r.user_id = ?
                WHERE m.course_id IN (' . $placeholders . ')
                  AND d.user_id <> ?
                  AND d.created_at > COALESCE(r.last_read_at, \'1970-01-01 00:00:00\')
                  AND NOT EXISTS (
                      SELECT 1
                      FROM user_roles ur
                      INNER JOIN roles ro ON ro.id = ur.role_id
                      WHERE ur.user_id = d.user_id
                        AND ro.name IN (\'instructor\', \'admin\')
                  )
                GROUP BY m.course_id';

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$userId], $courseIds, [$userId]);

        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $counts = [];

        foreach ($courseIds as $courseId) {
            $counts[$courseId] = 0;
        }

        foreach ($stmt->fetchAll() as $row) {
            $counts[(int) $row['course_id']] = (int) $row['unread_count'];
        }

        return $counts;
    }

    public function markModuleRead(int $userId, int $moduleId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO discussion_reads (user_id, module_id, last_read_at)
             VALUES (:user_id, :module_id, NOW())
             ON DUPLICATE KEY UPDATE last_read_at = NOW()'
        );
        $stmt->execute([
            'user_id' => $userId,
            'module_id' => $moduleId,
        ]);
    }
}
