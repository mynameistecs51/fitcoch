<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Question;
use App\Models\Quiz;

class QuizRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?Quiz
    {
        $stmt = $this->db->prepare('SELECT * FROM quizzes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Quiz::fromArray($row) : null;
    }

    public function findReadinessByModuleId(int $moduleId): ?Quiz
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM quizzes WHERE module_id = :module_id AND quiz_type = 'readiness' LIMIT 1"
        );
        $stmt->execute(['module_id' => $moduleId]);
        $row = $stmt->fetch();

        return $row ? Quiz::fromArray($row) : null;
    }

    /** @return array<int, Quiz> */
    public function listByModuleIds(array $moduleIds): array
    {
        if ($moduleIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM quizzes WHERE module_id IN ({$placeholders}) ORDER BY id ASC"
        );
        $stmt->execute(array_values($moduleIds));

        $quizzes = [];
        foreach ($stmt->fetchAll() as $row) {
            $quiz = Quiz::fromArray($row);
            $quizzes[$quiz->moduleId] = $quiz;
        }

        return $quizzes;
    }

    /** @return array<int, Question> */
    public function listQuestionsWithOptions(int $quizId, bool $includeCorrectFlags = false): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC'
        );
        $stmt->execute(['quiz_id' => $quizId]);
        $questions = [];

        foreach ($stmt->fetchAll() as $row) {
            $question = Question::fromArray($row);
            $options = $this->listOptionsForQuestion($question->id, $includeCorrectFlags);
            $questions[] = new Question(
                id: $question->id,
                quizId: $question->quizId,
                questionText: $question->questionText,
                questionType: $question->questionType,
                points: $question->points,
                options: $options,
            );
        }

        return $questions;
    }

    /**
     * @return array<int, array{option_number: int, option_text: string, is_correct?: bool}>
     */
    private function listOptionsForQuestion(int $questionId, bool $includeCorrectFlags): array
    {
        $stmt = $this->db->prepare(
            'SELECT option_number, option_text, is_correct
             FROM options
             WHERE question_id = :question_id
             ORDER BY option_number ASC'
        );
        $stmt->execute(['question_id' => $questionId]);

        return array_map(function (array $row) use ($includeCorrectFlags): array {
            $option = [
                'option_number' => (int) $row['option_number'],
                'option_text' => (string) $row['option_text'],
            ];

            if ($includeCorrectFlags) {
                $option['is_correct'] = (bool) $row['is_correct'];
            }

            return $option;
        }, $stmt->fetchAll());
    }

    /** @param array{module_id: int, quiz_type: string, title: string, passing_score_pct: int} $data */
    public function create(array $data): Quiz
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quizzes (module_id, quiz_type, title, passing_score_pct)
             VALUES (:module_id, :quiz_type, :title, :passing_score_pct)'
        );
        $stmt->execute($data);

        $quiz = $this->findById((int) $this->db->lastInsertId());

        if ($quiz === null) {
            throw new \RuntimeException('Failed to create quiz.');
        }

        return $quiz;
    }

    /** @param array{title: string, passing_score_pct: int} $data */
    public function update(int $quizId, array $data): Quiz
    {
        $stmt = $this->db->prepare(
            'UPDATE quizzes SET title = :title, passing_score_pct = :passing_score_pct WHERE id = :id'
        );
        $stmt->execute([
            'id' => $quizId,
            'title' => $data['title'],
            'passing_score_pct' => $data['passing_score_pct'],
        ]);

        $quiz = $this->findById($quizId);

        if ($quiz === null) {
            throw new \RuntimeException('Failed to update quiz.');
        }

        return $quiz;
    }

    public function delete(int $quizId): void
    {
        $stmt = $this->db->prepare('DELETE FROM quizzes WHERE id = :id');
        $stmt->execute(['id' => $quizId]);
    }

    public function countQuestions(int $quizId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM questions WHERE quiz_id = :quiz_id');
        $stmt->execute(['quiz_id' => $quizId]);

        return (int) $stmt->fetchColumn();
    }

    /** @param array{quiz_id: int, question_text: string, question_type: string, points: int} $data */
    public function createQuestion(array $data): Question
    {
        $stmt = $this->db->prepare(
            'INSERT INTO questions (quiz_id, question_text, question_type, points)
             VALUES (:quiz_id, :question_text, :question_type, :points)'
        );
        $stmt->execute($data);

        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $this->db->lastInsertId()]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Failed to create question.');
        }

        return Question::fromArray($row);
    }

    /** @param array{question_text: string, points: int} $data */
    public function updateQuestion(int $questionId, array $data): Question
    {
        $stmt = $this->db->prepare(
            'UPDATE questions SET question_text = :question_text, points = :points WHERE id = :id'
        );
        $stmt->execute([
            'id' => $questionId,
            'question_text' => $data['question_text'],
            'points' => $data['points'],
        ]);

        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $questionId]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Failed to update question.');
        }

        return Question::fromArray($row);
    }

    public function deleteQuestion(int $questionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM questions WHERE id = :id');
        $stmt->execute(['id' => $questionId]);
    }

    public function findQuestionById(int $questionId): ?Question
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $questionId]);
        $row = $stmt->fetch();

        return $row ? Question::fromArray($row) : null;
    }

    /** @param array<int, array{option_number: int, option_text: string, is_correct: bool}> $options */
    public function syncQuestionOptions(int $questionId, array $options): void
    {
        $this->db->pdo()->beginTransaction();

        try {
            $stmt = $this->db->prepare('DELETE FROM options WHERE question_id = :question_id');
            $stmt->execute(['question_id' => $questionId]);

            $insert = $this->db->prepare(
                'INSERT INTO options (question_id, option_number, option_text, is_correct)
                 VALUES (:question_id, :option_number, :option_text, :is_correct)'
            );

            foreach ($options as $option) {
                $insert->execute([
                    'question_id' => $questionId,
                    'option_number' => $option['option_number'],
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'] ? 1 : 0,
                ]);
            }

            $this->db->pdo()->commit();
        } catch (\Throwable $e) {
            $this->db->pdo()->rollBack();
            throw $e;
        }
    }
}
