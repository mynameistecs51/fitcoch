<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\KnowledgeItem;
use App\Models\SpacedRepSchedule;

class SpacedRepetitionRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, int> */
    public function listEnrolledCourseIds(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT co.course_id
             FROM cohort_enrollments ce
             INNER JOIN cohorts co ON co.id = ce.cohort_id
             WHERE ce.user_id = :user_id
               AND ce.status = \'active\''
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function countDueForUser(int $userId, string $today): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM spaced_rep_schedules
             WHERE user_id = :user_id
               AND next_review_date <= :today'
        );
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function ensureSchedulesForUser(int $userId, string $today): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO spaced_rep_schedules
                (user_id, knowledge_item_id, interval_days, easiness_factor, repetition_number, next_review_date)
             SELECT :user_id, ki.id, 1, 2.500, 0, :today
             FROM knowledge_items ki
             INNER JOIN cohorts co ON co.course_id = ki.course_id
             INNER JOIN cohort_enrollments ce ON ce.cohort_id = co.id
             WHERE ce.user_id = :user_id2
               AND ce.status = \'active\''
        );
        $stmt->execute([
            'user_id' => $userId,
            'user_id2' => $userId,
            'today' => $today,
        ]);
    }

    /**
     * @return array<int, array{
     *     item: KnowledgeItem,
     *     schedule: SpacedRepSchedule,
     *     course_title: string
     * }>
     */
    public function listDueForUser(int $userId, string $today): array
    {
        $stmt = $this->db->prepare(
            'SELECT ki.*, srs.*, c.title AS course_title
             FROM spaced_rep_schedules srs
             INNER JOIN knowledge_items ki ON ki.id = srs.knowledge_item_id
             INNER JOIN courses c ON c.id = ki.course_id
             WHERE srs.user_id = :user_id
               AND srs.next_review_date <= :today
             ORDER BY srs.next_review_date ASC, ki.id ASC'
        );
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today,
        ]);

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                'item' => KnowledgeItem::fromArray($row),
                'schedule' => SpacedRepSchedule::fromArray($row),
                'course_title' => (string) $row['course_title'],
            ];
        }

        return $rows;
    }

    public function findSchedule(int $userId, int $knowledgeItemId): ?SpacedRepSchedule
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM spaced_rep_schedules
             WHERE user_id = :user_id AND knowledge_item_id = :knowledge_item_id
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'knowledge_item_id' => $knowledgeItemId,
        ]);
        $row = $stmt->fetch();

        return $row ? SpacedRepSchedule::fromArray($row) : null;
    }

    public function findKnowledgeItem(int $knowledgeItemId): ?KnowledgeItem
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM knowledge_items WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $knowledgeItemId]);
        $row = $stmt->fetch();

        return $row ? KnowledgeItem::fromArray($row) : null;
    }

    /**
     * @param array{
     *     interval_days: int,
     *     easiness_factor: float,
     *     repetition_number: int,
     *     next_review_date: string
     * } $data
     */
    public function updateSchedule(int $userId, int $knowledgeItemId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE spaced_rep_schedules
             SET interval_days = :interval_days,
                 easiness_factor = :easiness_factor,
                 repetition_number = :repetition_number,
                 next_review_date = :next_review_date,
                 last_reviewed_at = NOW()
             WHERE user_id = :user_id
               AND knowledge_item_id = :knowledge_item_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'knowledge_item_id' => $knowledgeItemId,
            'interval_days' => $data['interval_days'],
            'easiness_factor' => $data['easiness_factor'],
            'repetition_number' => $data['repetition_number'],
            'next_review_date' => $data['next_review_date'],
        ]);
    }
}
