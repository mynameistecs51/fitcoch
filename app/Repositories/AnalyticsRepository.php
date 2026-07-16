<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AnalyticsRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function getTotalEnrolledCount(int $cohortId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total
             FROM cohort_enrollments
             WHERE cohort_id = :cohort_id AND status = \'active\''
        );
        $stmt->execute(['cohort_id' => $cohortId]);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function getCompletedPrepCount(int $cohortId, int $moduleId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS prepared
             FROM cohort_enrollments ce
             INNER JOIN readiness_tickets rt
                ON rt.user_id = ce.user_id
               AND rt.cohort_id = ce.cohort_id
               AND rt.module_id = :module_id
             WHERE ce.cohort_id = :cohort_id
               AND ce.status = \'active\'
               AND rt.status IN (\'unlocked\', \'overridden\')'
        );
        $stmt->execute([
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);

        return (int) ($stmt->fetch()['prepared'] ?? 0);
    }

    /**
     * @return array<int, array{
     *     question_id: int,
     *     question_text: string,
     *     total_responses: int,
     *     incorrect_count: int,
     *     incorrect_ratio: float
     * }>
     */
    public function findTopMisconceptions(int $cohortId, int $quizId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                q.id AS question_id,
                q.question_text,
                COUNT(qr.question_id) AS total_responses,
                SUM(CASE WHEN o.is_correct = 0 THEN 1 ELSE 0 END) AS incorrect_count
             FROM quiz_attempts qa
             INNER JOIN cohort_enrollments ce
                ON ce.user_id = qa.user_id
               AND ce.cohort_id = :cohort_id
               AND ce.status = \'active\'
             INNER JOIN quiz_responses qr ON qr.quiz_attempt_id = qa.id
             INNER JOIN questions q ON q.id = qr.question_id
             INNER JOIN options o
                ON o.question_id = qr.question_id
               AND o.option_number = qr.selected_option_number
             WHERE qa.quiz_id = :quiz_id
             GROUP BY q.id, q.question_text
             HAVING total_responses > 0
             ORDER BY (incorrect_count / total_responses) DESC, incorrect_count DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute([
            'cohort_id' => $cohortId,
            'quiz_id' => $quizId,
        ]);

        return array_map(static function (array $row): array {
            $total = (int) $row['total_responses'];
            $incorrect = (int) $row['incorrect_count'];

            return [
                'question_id' => (int) $row['question_id'],
                'question_text' => (string) $row['question_text'],
                'total_responses' => $total,
                'incorrect_count' => $incorrect,
                'incorrect_ratio' => $total > 0 ? round($incorrect / $total, 4) : 0.0,
            ];
        }, $stmt->fetchAll());
    }
}
