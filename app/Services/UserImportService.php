<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Support\SimpleSpreadsheet;
use Exception;

class UserImportService
{
    private const ALLOWED_ROLES = ['learner', 'instructor', 'admin'];
    private const HEADERS = ['student_id', 'title_prefix', 'first_name', 'last_name', 'password', 'role'];
    private const HEADER_ALIASES = [
        'student_id' => ['student_id', 'รหัสนักศึกษา'],
        'title_prefix' => ['title_prefix', 'คำนำหน้า'],
        'first_name' => ['first_name', 'ชื่อ'],
        'last_name' => ['last_name', 'นามสกุล'],
        'password' => ['password', 'รหัสผ่าน'],
        'role' => ['role', 'บทบาท'],
    ];
    private const MAX_FILE_BYTES = 2_097_152;

    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly RoleRepository $roleRepo,
    ) {
    }

    public function templateFilename(): string
    {
        return 'fitcoch_user_import_template.xlsx';
    }

    public function buildTemplateBinary(): string
    {
        return SimpleSpreadsheet::toXlsx(
            ['รหัสนักศึกษา', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'รหัสผ่าน', 'role'],
            [
                [
                    '6501234567',
                    'นาย',
                    'สมชาย',
                    'ใจดี',
                    'ChangeMe123!',
                    'learner',
                ],
            ]
        );
    }

    /** @param array<string, mixed> $file */
    public function importUploadedFile(array $file): array
    {
        $this->assertValidUpload($file);

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $rows = SimpleSpreadsheet::fromFile((string) $file['tmp_name'], $extension);

        return $this->importRows($rows);
    }

    /**
     * @param array<int, array<int, string>> $rows
     * @return array{created: int, skipped: int, errors: array<int, string>}
     */
    public function importRows(array $rows): array
    {
        if ($rows === []) {
            throw new Exception(__('admin.import.validation.empty_file'));
        }

        $headerRow = array_map(
            static fn (string $value): string => mb_strtolower(trim($value)),
            $rows[0]
        );

        $columnMap = $this->mapColumns($headerRow);
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $record = $this->extractRecord($row, $columnMap);

            try {
                $validated = $this->validateRecord($record, $rowNumber);

                if ($this->userRepo->studentIdExists($validated['student_id'])) {
                    $skipped++;
                    $errors[$rowNumber] = __('admin.import.validation.duplicate_student_id', [
                        'student_id' => $validated['student_id'],
                    ]);
                    continue;
                }

                $user = $this->userRepo->create([
                    'student_id' => $validated['student_id'],
                    'title_prefix' => $validated['title_prefix'],
                    'email' => strtolower($validated['student_id']) . '@student.fitcoch.local',
                    'password_hash' => password_hash($validated['password'], PASSWORD_ARGON2ID),
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'timezone' => default_timezone(),
                ]);

                $this->roleRepo->syncRoles($user->id, [$validated['role']]);
                $created++;
            } catch (Exception $e) {
                $errors[$rowNumber] = $e->getMessage();
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /** @param array<string, mixed> $file */
    private function assertValidUpload(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new Exception(__('admin.import.validation.file_required'));
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new Exception(__('admin.import.validation.upload_failed'));
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            throw new Exception(__('admin.import.validation.file_too_large'));
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            throw new Exception(__('admin.import.validation.invalid_type'));
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new Exception(__('admin.import.validation.upload_failed'));
        }
    }

    /**
     * @param array<int, string> $headerRow
     * @return array<string, int>
     */
    private function mapColumns(array $headerRow): array
    {
        $columnMap = [];

        foreach (self::HEADER_ALIASES as $header => $aliases) {
            $index = false;

            foreach ($aliases as $alias) {
                $index = array_search(mb_strtolower($alias), $headerRow, true);

                if ($index !== false) {
                    break;
                }
            }

            if ($index === false) {
                throw new Exception(__('admin.import.validation.missing_columns'));
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
     * @return array{student_id: string, title_prefix: string, first_name: string, last_name: string, password: string, role: string}
     */
    private function validateRecord(array $record, int $rowNumber): array
    {
        $studentId = strtoupper(trim($record['student_id'] ?? ''));
        $titlePrefix = trim($record['title_prefix'] ?? '');
        $firstName = trim($record['first_name'] ?? '');
        $lastName = trim($record['last_name'] ?? '');
        $password = $record['password'] ?? '';
        $role = strtolower($record['role'] !== '' ? $record['role'] : 'learner');

        if ($studentId === '') {
            throw new Exception(__('admin.import.validation.row_student_id', ['row' => $rowNumber]));
        }

        if (!preg_match('/^[A-Za-z0-9_-]{3,20}$/', $studentId)) {
            throw new Exception(__('admin.import.validation.row_student_id_invalid', ['row' => $rowNumber]));
        }

        if ($titlePrefix === '') {
            throw new Exception(__('admin.import.validation.row_title_prefix', ['row' => $rowNumber]));
        }

        if ($firstName === '') {
            throw new Exception(__('admin.import.validation.row_first_name', ['row' => $rowNumber]));
        }

        if ($lastName === '') {
            throw new Exception(__('admin.import.validation.row_last_name', ['row' => $rowNumber]));
        }

        $passwordErrors = $this->validatePassword($password);

        if ($passwordErrors !== []) {
            throw new Exception(__('admin.import.validation.row_password', [
                'row' => $rowNumber,
                'details' => implode(' ', $passwordErrors),
            ]));
        }

        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new Exception(__('admin.import.validation.row_role', ['row' => $rowNumber]));
        }

        return [
            'student_id' => $studentId,
            'title_prefix' => $titlePrefix,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => $password,
            'role' => $role,
        ];
    }

    /** @return array<int, string> */
    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 10) {
            $errors[] = __('validation.password_min');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = __('validation.password_upper');
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = __('validation.password_lower');
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = __('validation.password_number');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = __('validation.password_special');
        }

        return $errors;
    }
}
