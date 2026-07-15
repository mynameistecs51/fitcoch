<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\SpacedRepetitionRepository;
use DateTime;
use DateTimeZone;
use Exception;

class SpacedRepetitionService
{
    private const MIN_EF = 1.3;

    public function __construct(
        private readonly SpacedRepetitionRepository $repo,
    ) {
    }

    /**
     * @return array{
     *     due_count: int,
     *     remaining_count: int,
     *     current: ?array{
     *         item: \App\Models\KnowledgeItem,
     *         schedule: \App\Models\SpacedRepSchedule,
     *         course_title: string
     *     }
     * }
     */
    public function getDailyPanel(User $user): array
    {
        $today = $this->todayForUser($user);
        $this->repo->ensureSchedulesForUser($user->id, $today);
        $dueItems = $this->repo->listDueForUser($user->id, $today);
        $current = $dueItems[0] ?? null;

        return [
            'due_count' => count($dueItems),
            'remaining_count' => count($dueItems),
            'current' => $current,
        ];
    }

    public function countDueToday(User $user): int
    {
        $today = $this->todayForUser($user);
        $this->repo->ensureSchedulesForUser($user->id, $today);

        return $this->repo->countDueForUser($user->id, $today);
    }

    /**
     * @return array{
     *     next_review_date: string,
     *     interval_days: int,
     *     easiness_factor: float,
     *     repetition_number: int
     * }
     */
    public function submitRating(User $user, int $knowledgeItemId, int $rating): array
    {
        if ($rating < 0 || $rating > 5) {
            throw new ValidationException(__('reviews.validation.rating_invalid'), [
                'rating' => [__('reviews.validation.rating_invalid')],
            ]);
        }

        $item = $this->repo->findKnowledgeItem($knowledgeItemId);

        if ($item === null) {
            throw new Exception(__('reviews.validation.item_not_found'));
        }

        $schedule = $this->repo->findSchedule($user->id, $knowledgeItemId);

        if ($schedule === null) {
            throw new Exception(__('reviews.validation.schedule_not_found'));
        }

        $today = $this->todayForUser($user);

        if ($schedule->nextReviewDate > $today) {
            throw new Exception(__('reviews.validation.not_due'));
        }

        $result = $this->calculateSM2(
            $schedule->easinessFactor,
            $schedule->repetitionNumber,
            $schedule->intervalDays,
            $rating,
        );

        $nextReviewDate = $this->addDays($today, $result['interval_days'], $user->timezone);

        $this->repo->updateSchedule($user->id, $knowledgeItemId, [
            'interval_days' => $result['interval_days'],
            'easiness_factor' => $result['easiness_factor'],
            'repetition_number' => $result['repetition_number'],
            'next_review_date' => $nextReviewDate,
        ]);

        return [
            'next_review_date' => $nextReviewDate,
            'interval_days' => $result['interval_days'],
            'easiness_factor' => $result['easiness_factor'],
            'repetition_number' => $result['repetition_number'],
        ];
    }

    /**
     * @return array{
     *     interval_days: int,
     *     easiness_factor: float,
     *     repetition_number: int
     * }
     */
    public function calculateSM2(float $oldEF, int $repetitionCount, int $intervalDays, int $rating): array
    {
        if ($rating < 0 || $rating > 5) {
            $rating = 0;
        }

        $newEF = $oldEF + (0.1 - (5 - $rating) * (0.08 + (5 - $rating) * 0.02));

        if ($newEF < self::MIN_EF) {
            $newEF = self::MIN_EF;
        }

        if ($rating >= 3) {
            if ($repetitionCount === 0) {
                $nextInterval = 1;
                $newRepetition = 1;
            } elseif ($repetitionCount === 1) {
                $nextInterval = 6;
                $newRepetition = 2;
            } else {
                $nextInterval = (int) ceil($intervalDays * $newEF);
                $newRepetition = $repetitionCount + 1;
            }
        } else {
            $nextInterval = 1;
            $newRepetition = 0;
        }

        return [
            'interval_days' => $nextInterval,
            'easiness_factor' => $newEF,
            'repetition_number' => $newRepetition,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listDailyQueue(User $user): array
    {
        $today = $this->todayForUser($user);
        $this->repo->ensureSchedulesForUser($user->id, $today);

        return array_map(static function (array $row): array {
            return [
                'knowledge_item_id' => $row['item']->id,
                'concept_name' => $row['item']->conceptName,
                'description' => $row['item']->description,
                'course_title' => $row['course_title'],
                'interval_days' => $row['schedule']->intervalDays,
                'easiness_factor' => $row['schedule']->easinessFactor,
                'repetition_number' => $row['schedule']->repetitionNumber,
                'next_review_date' => $row['schedule']->nextReviewDate,
            ];
        }, $this->repo->listDueForUser($user->id, $today));
    }

    public function todayForUser(User $user): string
    {
        $timezone = $user->timezone !== '' ? $user->timezone : 'Asia/Bangkok';

        try {
            $tz = new DateTimeZone($timezone);
        } catch (Exception) {
            $tz = new DateTimeZone('Asia/Bangkok');
        }

        return (new DateTime('now', $tz))->format('Y-m-d');
    }

    private function addDays(string $date, int $days, string $timezone): string
    {
        try {
            $tz = new DateTimeZone($timezone !== '' ? $timezone : 'Asia/Bangkok');
        } catch (Exception) {
            $tz = new DateTimeZone('Asia/Bangkok');
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date, $tz);

        if ($dt === false) {
            $dt = new DateTime('now', $tz);
        }

        $dt->modify('+' . $days . ' day');

        return $dt->format('Y-m-d');
    }
}
