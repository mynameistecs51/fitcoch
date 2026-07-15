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

    public function findActiveEnrollmentForUser(int $userId, int $courseId): ?Cohort
    {
        $stmt = $this->db->prepare(
            'SELECT co.*
             FROM cohorts co
             INNER JOIN cohort_enrollments ce ON ce.cohort_id = co.id
             WHERE ce.user_id = :user_id
               AND co.course_id = :course_id
               AND ce.status = \'active\'
             ORDER BY ce.enrolled_at DESC
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        $row = $stmt->fetch();

        return $row ? Cohort::fromArray($row) : null;
    }

    public function findById(int $id): ?Cohort
    {
        $stmt = $this->db->prepare('SELECT * FROM cohorts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Cohort::fromArray($row) : null;
    }

    public function enrollUser(int $cohortId, int $userId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cohort_enrollments (cohort_id, user_id, status)
             VALUES (:cohort_id, :user_id, \'active\')
             ON DUPLICATE KEY UPDATE status = \'active\''
        );
        $stmt->execute([
            'cohort_id' => $cohortId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @return array<int, array{
     *     user_id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     enrolled_at: string,
     *     status: string
     * }>
     */
    public function listActiveEnrollments(int $cohortId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                ce.user_id,
                ce.enrolled_at,
                ce.status,
                u.first_name,
                u.last_name,
                u.email
             FROM cohort_enrollments ce
             INNER JOIN users u ON u.id = ce.user_id
             WHERE ce.cohort_id = :cohort_id
               AND ce.status = \'active\'
             ORDER BY u.first_name ASC, u.last_name ASC, u.email ASC'
        );
        $stmt->execute(['cohort_id' => $cohortId]);

        return $stmt->fetchAll();
    }

    /**
     * @param array<int, int> $courseIds
     * @return array<int, int>
     */
    public function countActiveEnrollmentsByCourseIds(array $courseIds): array
    {
        if ($courseIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT co.course_id, COUNT(DISTINCT ce.user_id) AS enrolled_count
             FROM cohort_enrollments ce
             INNER JOIN cohorts co ON co.id = ce.cohort_id
             WHERE ce.status = 'active'
               AND co.course_id IN ({$placeholders})
             GROUP BY co.course_id"
        );
        $stmt->execute($courseIds);

        $counts = [];

        foreach ($stmt->fetchAll() as $row) {
            $counts[(int) $row['course_id']] = (int) $row['enrolled_count'];
        }

        return $counts;
    }
}
