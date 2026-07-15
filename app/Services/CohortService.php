<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Course;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use Exception;

class CohortService
{
    public function __construct(
        private readonly CourseRepository $courseRepo,
        private readonly CohortRepository $cohortRepo,
        private readonly UserRepository $userRepo,
    ) {
    }

    /**
     * @return array{
     *     course: Course,
     *     cohorts: array<int, array{
     *         cohort: Cohort,
     *         enrollments: array<int, array<string, mixed>>,
     *         enrollment_count: int
     *     }>,
     *     available_learners: array<int, array{user_id: int, first_name: string, last_name: string, email: string}>
     * }|null
     */
    public function getCourseCohortsPanel(int $courseId): ?array
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            return null;
        }

        $cohorts = [];
        $enrolledUserIds = [];

        foreach ($this->cohortRepo->listByCourseId($courseId) as $cohort) {
            $enrollments = $this->cohortRepo->listActiveEnrollments($cohort->id);

            foreach ($enrollments as $enrollment) {
                $enrolledUserIds[(int) $enrollment['user_id']] = true;
            }

            $cohorts[] = [
                'cohort' => $cohort,
                'enrollments' => $enrollments,
                'enrollment_count' => count($enrollments),
            ];
        }

        $availableLearners = [];

        foreach ($this->userRepo->listWithRoles() as $entry) {
            if (!in_array('learner', $entry['roles'], true)) {
                continue;
            }

            if (isset($enrolledUserIds[$entry['user']->id])) {
                continue;
            }

            $availableLearners[] = [
                'user_id' => $entry['user']->id,
                'first_name' => $entry['user']->firstName,
                'last_name' => $entry['user']->lastName,
                'email' => $entry['user']->email,
            ];
        }

        return [
            'course' => $course,
            'cohorts' => $cohorts,
            'available_learners' => $availableLearners,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCohort(int $courseId, array $data): Cohort
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateCohortData($data);

        return $this->cohortRepo->create([
            'course_id' => $courseId,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCohort(int $cohortId, array $data): Cohort
    {
        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null) {
            throw new Exception(__('cohorts.validation.not_found'));
        }

        $validated = $this->validateCohortData($data);

        return $this->cohortRepo->update($cohortId, $validated);
    }

    public function enrollLearner(int $cohortId, int $userId): void
    {
        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null) {
            throw new Exception(__('cohorts.validation.not_found'));
        }

        $user = $this->userRepo->findById($userId);

        if ($user === null) {
            throw new Exception(__('cohorts.validation.learner_not_found'));
        }

        $this->cohortRepo->enrollUser($cohortId, $userId);
    }

    public function dropLearner(int $cohortId, int $userId): void
    {
        $cohort = $this->cohortRepo->findById($cohortId);

        if ($cohort === null) {
            throw new Exception(__('cohorts.validation.not_found'));
        }

        if (!$this->cohortRepo->isUserEnrolled($cohortId, $userId)) {
            throw new Exception(__('cohorts.validation.learner_not_enrolled'));
        }

        $this->cohortRepo->setEnrollmentStatus($cohortId, $userId, 'dropped');
    }

    /**
     * @param array<string, mixed> $data
     * @return array{name: string, start_date: string, end_date: string}
     */
    private function validateCohortData(array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            $errors['name'][] = __('cohorts.validation.name_required');
        } elseif (mb_strlen($name) > 100) {
            $errors['name'][] = __('cohorts.validation.name_max');
        }

        $startDate = trim((string) ($data['start_date'] ?? ''));
        $endDate = trim((string) ($data['end_date'] ?? ''));

        if ($startDate === '' || strtotime($startDate) === false) {
            $errors['start_date'][] = __('cohorts.validation.start_date_invalid');
        }

        if ($endDate === '' || strtotime($endDate) === false) {
            $errors['end_date'][] = __('cohorts.validation.end_date_invalid');
        }

        if ($startDate !== '' && $endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            $errors['end_date'][] = __('cohorts.validation.end_before_start');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'name' => $name,
            'start_date' => date('Y-m-d', strtotime($startDate)),
            'end_date' => date('Y-m-d', strtotime($endDate)),
        ];
    }
}
