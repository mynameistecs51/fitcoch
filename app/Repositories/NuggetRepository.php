<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Nugget;

class NuggetRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?Nugget
    {
        $stmt = $this->db->prepare('SELECT * FROM nuggets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Nugget::fromArray($row) : null;
    }

    /** @return array<int, Nugget> */
    public function listByModuleId(int $moduleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM nuggets WHERE module_id = :module_id ORDER BY sequence_order ASC'
        );
        $stmt->execute(['module_id' => $moduleId]);

        return array_map(
            static fn (array $row): Nugget => Nugget::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function nextSequenceOrder(int $moduleId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(MAX(sequence_order), 0) + 1 FROM nuggets WHERE module_id = :module_id'
        );
        $stmt->execute(['module_id' => $moduleId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array{
     *     module_id: int,
     *     title: string,
     *     nugget_type: string,
     *     content_url: ?string,
     *     content_body: ?string,
     *     duration_seconds: int,
     *     sequence_order: int
     * } $data
     */
    public function create(array $data): Nugget
    {
        $stmt = $this->db->prepare(
            'INSERT INTO nuggets (module_id, title, nugget_type, content_url, content_body, duration_seconds, sequence_order)
             VALUES (:module_id, :title, :nugget_type, :content_url, :content_body, :duration_seconds, :sequence_order)'
        );
        $stmt->execute([
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'nugget_type' => $data['nugget_type'],
            'content_url' => $data['content_url'],
            'content_body' => $data['content_body'],
            'duration_seconds' => $data['duration_seconds'],
            'sequence_order' => $data['sequence_order'],
        ]);

        $nugget = $this->findById((int) $this->db->lastInsertId());

        if ($nugget === null) {
            throw new \RuntimeException('Failed to create nugget.');
        }

        return $nugget;
    }

    /**
     * @param array{title: string, content_url: ?string, duration_seconds: int} $data
     */
    public function update(int $id, array $data): Nugget
    {
        $stmt = $this->db->prepare(
            'UPDATE nuggets
             SET title = :title, content_url = :content_url, duration_seconds = :duration_seconds
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'content_url' => $data['content_url'],
            'duration_seconds' => $data['duration_seconds'],
        ]);

        $nugget = $this->findById($id);

        if ($nugget === null) {
            throw new \RuntimeException('Failed to update nugget.');
        }

        return $nugget;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM nuggets WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
