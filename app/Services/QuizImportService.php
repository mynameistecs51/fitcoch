<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\SimpleSpreadsheet;
use Exception;

class QuizImportService
{
    private const HEADERS = [
        'question_text',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'correct_option',
        'points',
    ];
    private const MAX_FILE_BYTES = 2_097_152;

    public function __construct(
        private readonly QuizService $quizService,
    ) {
    }

    public function templateFilename(): string
    {
        return 'fitcoch_quiz_import_template.xlsx';
    }

    public function buildTemplateBinary(): string
    {
        return SimpleSpreadsheet::toXlsx(self::HEADERS, [
            [
                'What does the first T in FITT-VP stand for?',
                'Type',
                'Time',
                'Tension',
                'Temperature',
                '1',
                '10',
            ],
            [
                'Which component is NOT part of FITT-VP?',
                'Frequency',
                'Intensity',
                'Hydration',
                'Volume',
                '3',
                '10',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $file
     * @return array{created: int, errors: array<int, string>}
     */
    public function importUploadedFile(int $courseId, int $moduleId, int $quizId, array $file): array
    {
        $this->assertValidUpload($file);

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $rows = SimpleSpreadsheet::fromFile((string) $file['tmp_name'], $extension);

        return $this->importRows($courseId, $moduleId, $quizId, $rows);
    }

    /**
     * @param array<int, array<int, string>> $rows
     * @return array{created: int, errors: array<int, string>}
     */
    public function importRows(int $courseId, int $moduleId, int $quizId, array $rows): array
    {
        if ($rows === []) {
            throw new Exception(__('quizzes.import.validation.empty_file'));
        }

        $headerRow = array_map(
            static fn (string $value): string => strtolower(trim($value)),
            $rows[0]
        );
        $columnMap = $this->mapColumns($headerRow);
        $questions = [];
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $record = $this->extractRecord($row, $columnMap);

            try {
                $questions[] = $this->validateRecord($record, $rowNumber);
            } catch (Exception $e) {
                $errors[$rowNumber] = $e->getMessage();
            }
        }

        if ($questions === [] && $errors === []) {
            throw new Exception(__('quizzes.import.validation.no_rows'));
        }

        if ($errors !== []) {
            return [
                'created' => 0,
                'errors' => $errors,
            ];
        }

        $created = $this->quizService->saveQuestions($courseId, $moduleId, $quizId, [
            'questions' => $questions,
        ]);

        return [
            'created' => count($created),
            'errors' => [],
        ];
    }

    /** @param array<string, mixed> $file */
    private function assertValidUpload(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new Exception(__('quizzes.import.validation.file_required'));
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception(__('quizzes.import.validation.upload_failed'));
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            throw new Exception(__('quizzes.import.validation.file_too_large'));
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            throw new Exception(__('quizzes.import.validation.invalid_type'));
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new Exception(__('quizzes.import.validation.upload_failed'));
        }
    }

    /**
     * @param array<int, string> $headerRow
     * @return array<string, int>
     */
    private function mapColumns(array $headerRow): array
    {
        $columnMap = [];

        foreach (self::HEADERS as $header) {
            $index = array_search($header, $headerRow, true);

            if ($index === false) {
                throw new Exception(__('quizzes.import.validation.missing_columns'));
            }

            $columnMap[$header] = (int) $index;
        }

        return $columnMap;
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $columnMap
     * @return array<string, string>
     */
    private function extractRecord(array $row, array $columnMap): array
    {
        $record = [];

        foreach ($columnMap as $header => $index) {
            $record[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $record;
    }

    /** @param array<int, string> $row */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, string> $record
     * @return array<string, mixed>
     */
    private function validateRecord(array $record, int $rowNumber): array
    {
        $questionText = $record['question_text'] ?? '';
        $correctOption = (int) ($record['correct_option'] !== '' ? $record['correct_option'] : 0);
        $points = (int) ($record['points'] !== '' ? $record['points'] : 10);

        if ($questionText === '') {
            throw new Exception(__('quizzes.import.validation.row_question_text', ['row' => $rowNumber]));
        }

        for ($i = 1; $i <= 4; $i++) {
            if (($record['option_' . $i] ?? '') === '') {
                throw new Exception(__('quizzes.import.validation.row_option', [
                    'row' => $rowNumber,
                    'option' => $i,
                ]));
            }
        }

        if ($correctOption < 1 || $correctOption > 4) {
            throw new Exception(__('quizzes.import.validation.row_correct_option', ['row' => $rowNumber]));
        }

        if ($points < 1 || $points > 100) {
            throw new Exception(__('quizzes.import.validation.row_points', ['row' => $rowNumber]));
        }

        return [
            'question_text' => $questionText,
            'option_1' => $record['option_1'],
            'option_2' => $record['option_2'],
            'option_3' => $record['option_3'],
            'option_4' => $record['option_4'],
            'correct_option' => $correctOption,
            'points' => $points,
        ];
    }
}
