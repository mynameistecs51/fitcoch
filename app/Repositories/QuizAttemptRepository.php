<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class QuizAttemptRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * @param array<int, array{question_id: int, selected_option_number: int}> $responses
     * @return array{id: int, score_pct: int, completed_at: string}
     */
    public function create(int $userId, int $quizId, int $scorePct, array $responses): array
    {
        $this->db->pdo()->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO quiz_attempts (user_id, quiz_id, score_pct)
                 VALUES (:user_id, :quiz_id, :score_pct)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'score_pct' => $scorePct,
            ]);

            $attemptId = (int) $this->db->lastInsertId();

            $responseStmt = $this->db->prepare(
                'INSERT INTO quiz_responses (quiz_attempt_id, question_id, selected_option_number)
                 VALUES (:quiz_attempt_id, :question_id, :selected_option_number)'
            );

            foreach ($responses as $response) {
                $responseStmt->execute([
                    'quiz_attempt_id' => $attemptId,
                    'question_id' => $response['question_id'],
                    'selected_option_number' => $response['selected_option_number'],
                ]);
            }

            $this->db->pdo()->commit();
        } catch (\Throwable $e) {
            $this->db->pdo()->rollBack();
            throw $e;
        }

        $stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $attemptId]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Failed to create quiz attempt.');
        }

        return [
            'id' => (int) $row['id'],
            'score_pct' => (int) $row['score_pct'],
            'completed_at' => (string) $row['completed_at'],
        ];
    }

    /**
     * @param array<int, int> $quizIds
     * @return array<int, array{id: int, quiz_id: int, score_pct: int, completed_at: string}>
     */
    public function findLatestByUserAndQuizIds(int $userId, array $quizIds): array
    {
        if ($quizIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($quizIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT qa.id, qa.quiz_id, qa.score_pct, qa.completed_at
             FROM quiz_attempts qa
             INNER JOIN (
                SELECT quiz_id, MAX(id) AS latest_id
                FROM quiz_attempts
                WHERE user_id = ? AND quiz_id IN ({$placeholders})
                GROUP BY quiz_id
             ) latest ON qa.id = latest.latest_id"
        );
        $stmt->execute(array_merge([$userId], $quizIds));

        $indexed = [];

        foreach ($stmt->fetchAll() as $row) {
            $indexed[(int) $row['quiz_id']] = [
                'id' => (int) $row['id'],
                'quiz_id' => (int) $row['quiz_id'],
                'score_pct' => (int) $row['score_pct'],
                'completed_at' => (string) $row['completed_at'],
            ];
        }

        return $indexed;
    }

    /**
     * @param array<int, int> $quizIds
     * @return array<int, array<int, array{id: int, quiz_id: int, score_pct: int, completed_at: string}>>
     */
    public function findLatestByCohortAndQuizIds(int $cohortId, array $quizIds): array
    {
        if ($quizIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($quizIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT qa.id, qa.user_id, qa.quiz_id, qa.score_pct, qa.completed_at
             FROM quiz_attempts qa
             INNER JOIN (
                SELECT qa2.user_id, qa2.quiz_id, MAX(qa2.id) AS latest_id
                FROM quiz_attempts qa2
                INNER JOIN cohort_enrollments ce
                    ON ce.user_id = qa2.user_id
                   AND ce.cohort_id = ?
                   AND ce.status = 'active'
                WHERE qa2.quiz_id IN ({$placeholders})
                GROUP BY qa2.user_id, qa2.quiz_id
             ) latest ON qa.id = latest.latest_id"
        );
        $stmt->execute(array_merge([$cohortId], $quizIds));

        $indexed = [];

        foreach ($stmt->fetchAll() as $row) {
            $userId = (int) $row['user_id'];
            $indexed[$userId][(int) $row['quiz_id']] = [
                'id' => (int) $row['id'],
                'quiz_id' => (int) $row['quiz_id'],
                'score_pct' => (int) $row['score_pct'],
                'completed_at' => (string) $row['completed_at'],
            ];
        }

        return $indexed;
    }

    public function hasPassingAttempt(int $userId, int $quizId, int $passingScorePct): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM quiz_attempts
             WHERE user_id = :user_id AND quiz_id = :quiz_id AND score_pct >= :passing_score
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'passing_score' => $passingScorePct,
        ]);

        return (bool) $stmt->fetch();
    }
}
