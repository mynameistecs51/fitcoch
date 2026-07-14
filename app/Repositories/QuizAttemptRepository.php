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
}
