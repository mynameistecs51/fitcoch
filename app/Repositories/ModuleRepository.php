<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Module;

class ModuleRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?Module
    {
        $stmt = $this->db->prepare('SELECT * FROM modules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Module::fromArray($row) : null;
    }

    /** @return array<int, Module> */
    public function listByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM modules WHERE course_id = :course_id ORDER BY sequence_order ASC'
        );
        $stmt->execute(['course_id' => $courseId]);

        return array_map(
            static fn (array $row): Module => Module::fromArray($row),
            $stmt->fetchAll()
        );
    }

    public function nextSequenceOrder(int $courseId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(MAX(sequence_order), 0) + 1 FROM modules WHERE course_id = :course_id'
        );
        $stmt->execute(['course_id' => $courseId]);

        return (int) $stmt->fetchColumn();
    }

    /** @param array{course_id: int, title: string, sequence_order: int} $data */
    public function create(array $data): Module
    {
        $stmt = $this->db->prepare(
            'INSERT INTO modules (course_id, title, sequence_order)
             VALUES (:course_id, :title, :sequence_order)'
        );
        $stmt->execute([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'sequence_order' => $data['sequence_order'],
        ]);

        $module = $this->findById((int) $this->db->lastInsertId());

        if ($module === null) {
            throw new \RuntimeException('Failed to create module.');
        }

        return $module;
    }

    /** @param array{title: string, sequence_order: int} $data */
    public function update(int $id, array $data): Module
    {
        $stmt = $this->db->prepare(
            'UPDATE modules SET title = :title, sequence_order = :sequence_order WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'sequence_order' => $data['sequence_order'],
        ]);

        $module = $this->findById($id);

        if ($module === null) {
            throw new \RuntimeException('Failed to update module.');
        }

        return $module;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM modules WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
