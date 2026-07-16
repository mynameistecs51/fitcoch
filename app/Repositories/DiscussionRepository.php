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
}
