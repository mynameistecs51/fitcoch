<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Course;

class CourseRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?Course
    {
        $stmt = $this->db->prepare('SELECT * FROM courses WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Course::fromArray($row) : null;
    }

    /** @return array<int, Course> */
    public function listPublished(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM courses WHERE status = 'published' ORDER BY title ASC"
        );
        $stmt->execute();

        return array_map(
            static fn (array $row): Course => Course::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @return array<int, Course> */
    public function listEnrolledForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*
             FROM courses c
             INNER JOIN cohorts co ON co.course_id = c.id
             INNER JOIN cohort_enrollments ce ON ce.cohort_id = co.id
             WHERE ce.user_id = :user_id
               AND ce.status = \'active\'
               AND c.status = \'published\'
             GROUP BY c.id
             ORDER BY c.title ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(
            static fn (array $row): Course => Course::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @return array<int, Course> */
    public function listAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM courses ORDER BY updated_at DESC');
        $stmt->execute();

        return array_map(
            static fn (array $row): Course => Course::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @param array{title: string, description: ?string, status: string} $data */
    public function create(array $data): Course
    {
        $stmt = $this->db->prepare(
            'INSERT INTO courses (title, description, status)
             VALUES (:title, :description, :status)'
        );
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        $course = $this->findById((int) $this->db->lastInsertId());

        if ($course === null) {
            throw new \RuntimeException('Failed to create course.');
        }

        return $course;
    }

    /** @param array{title: string, description: ?string, status: string} $data */
    public function update(int $id, array $data): Course
    {
        $stmt = $this->db->prepare(
            'UPDATE courses
             SET title = :title, description = :description, status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        $course = $this->findById($id);

        if ($course === null) {
            throw new \RuntimeException('Failed to update course.');
        }

        return $course;
    }

    public function isUserEnrolled(int $userId, int $courseId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM cohort_enrollments ce
             INNER JOIN cohorts co ON co.id = ce.cohort_id
             WHERE ce.user_id = :user_id
               AND co.course_id = :course_id
               AND ce.status = \'active\'
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
