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
}
