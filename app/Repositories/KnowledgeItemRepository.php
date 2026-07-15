<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\KnowledgeItem;

class KnowledgeItemRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, KnowledgeItem> */
    public function listByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM knowledge_items WHERE course_id = :course_id ORDER BY id ASC'
        );
        $stmt->execute(['course_id' => $courseId]);

        return array_map(
            static fn (array $row): KnowledgeItem => KnowledgeItem::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function findById(int $id): ?KnowledgeItem
    {
        $stmt = $this->db->prepare('SELECT * FROM knowledge_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? KnowledgeItem::fromArray($row) : null;
    }

    public function existsByCourseAndConcept(int $courseId, string $conceptName): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM knowledge_items
             WHERE course_id = :course_id AND concept_name = :concept_name
             LIMIT 1'
        );
        $stmt->execute([
            'course_id' => $courseId,
            'concept_name' => $conceptName,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array{course_id: int, concept_name: string, description: ?string} $data
     */
    public function create(array $data): KnowledgeItem
    {
        $stmt = $this->db->prepare(
            'INSERT INTO knowledge_items (course_id, concept_name, description)
             VALUES (:course_id, :concept_name, :description)'
        );
        $stmt->execute([
            'course_id' => $data['course_id'],
            'concept_name' => $data['concept_name'],
            'description' => $data['description'],
        ]);

        return $this->findById((int) $this->db->lastInsertId())
            ?? throw new \RuntimeException('Failed to create knowledge item.');
    }

    /**
     * @param array{concept_name: string, description: ?string} $data
     */
    public function update(int $id, array $data): KnowledgeItem
    {
        $stmt = $this->db->prepare(
            'UPDATE knowledge_items
             SET concept_name = :concept_name, description = :description
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'concept_name' => $data['concept_name'],
            'description' => $data['description'],
        ]);

        return $this->findById($id)
            ?? throw new \RuntimeException('Failed to update knowledge item.');
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM knowledge_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
