<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Cohort;

class CohortRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, Cohort> */
    public function listByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cohorts WHERE course_id = :course_id ORDER BY start_date DESC'
        );
        $stmt->execute(['course_id' => $courseId]);

        return array_map(
            static fn (array $row): Cohort => Cohort::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @param array{course_id: int, name: string, start_date: string, end_date: string} $data */
    public function create(array $data): Cohort
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cohorts (course_id, name, start_date, end_date)
             VALUES (:course_id, :name, :start_date, :end_date)'
        );
        $stmt->execute($data);

        $stmt = $this->db->prepare('SELECT * FROM cohorts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $this->db->lastInsertId()]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Failed to create cohort.');
        }

        return Cohort::fromArray($row);
    }
}
