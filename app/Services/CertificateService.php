<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Models\User;
use App\Repositories\CertificateRepository;
use App\Repositories\GamificationRepository;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\UserRepository;
use Exception;

class CertificateService
{
    public function __construct(
        private readonly CertificateRepository $certificateRepo,
        private readonly CourseService $courseService,
        private readonly QuizService $quizService,
        private readonly NuggetProgressRepository $progressRepo,
        private readonly QuizAttemptRepository $attemptRepo,
        private readonly UserRepository $userRepo,
        private readonly GamificationRepository $gamificationRepo,
    ) {
    }

    /**
     * @return array{
     *     certificate: Certificate,
     *     user: User,
     *     course: Course,
     *     badges: array<int, array<string, mixed>>
     * }|null
     */
    public function getVerificationDetails(string $hash): ?array
    {
        $certificate = $this->certificateRepo->findByHash($hash);

        if ($certificate === null) {
            return null;
        }

        return $this->buildDetails($certificate);
    }

    /**
     * @return array{
     *     certificate: Certificate,
     *     user: User,
     *     course: Course,
     *     badges: array<int, array<string, mixed>>
     * }|null
     */
    public function issueForLearner(int $userId, int $courseId): ?array
    {
        $existing = $this->certificateRepo->findByUserAndCourse($userId, $courseId);

        if ($existing !== null) {
            return $this->buildDetails($existing);
        }

        if (!$this->isEligible($userId, $courseId)) {
            return null;
        }

        $hash = $this->generateVerificationHash($userId, $courseId);
        $certificate = $this->certificateRepo->create($userId, $courseId, $hash);

        return $this->buildDetails($certificate);
    }

    public function isEligible(int $userId, int $courseId): bool
    {
        $outline = $this->courseService->getCourseOutline($courseId, $userId);

        if ($outline === null) {
            return false;
        }

        $moduleIds = array_map(static fn ($module) => $module->id, $outline['modules']);

        if ($moduleIds === []) {
            return false;
        }

        $quizzesByModule = $this->quizService->listQuizzesByModuleIds($moduleIds);
        $quizIds = array_values(array_map(
            static fn (Quiz $quiz): int => $quiz->id,
            array_filter($quizzesByModule),
        ));
        $latestAttempts = $this->attemptRepo->findLatestByUserAndQuizIds($userId, $quizIds);

        $videoNuggets = [];

        foreach ($outline['modules'] as $module) {
            foreach ($outline['nuggetsByModule'][$module->id] ?? [] as $nugget) {
                if ($nugget->nuggetType === 'video') {
                    $videoNuggets[] = $nugget;
                }
            }
        }

        if ($videoNuggets === [] && $quizIds === []) {
            return false;
        }

        $nuggetIds = array_map(static fn (Nugget $nugget): int => $nugget->id, $videoNuggets);
        $progressByNugget = $this->progressRepo->listByUserAndNuggetIds($userId, $nuggetIds);

        foreach ($videoNuggets as $nugget) {
            $progress = $progressByNugget[$nugget->id] ?? null;

            if (($progress['status'] ?? '') !== 'completed') {
                return false;
            }
        }

        foreach ($quizzesByModule as $quiz) {
            $attempt = $latestAttempts[$quiz->id] ?? null;

            if ($attempt === null || $attempt['score_pct'] < $quiz->passingScorePct) {
                return false;
            }
        }

        return true;
    }

    public function findLearnerCertificate(int $userId, int $courseId): ?Certificate
    {
        return $this->certificateRepo->findByUserAndCourse($userId, $courseId);
    }

    /**
     * @return array{
     *     certificate: Certificate,
     *     user: User,
     *     course: Course,
     *     badges: array<int, array<string, mixed>>
     * }
     */
    private function buildDetails(Certificate $certificate): array
    {
        $user = $this->userRepo->findById($certificate->userId);
        $outline = $this->courseService->getCourseOutline($certificate->courseId, $certificate->userId);

        if ($user === null || $outline === null) {
            throw new Exception(__('certificates.validation.not_found'));
        }

        $badges = $this->gamificationRepo->listUserBadges($certificate->userId);

        return [
            'certificate' => $certificate,
            'user' => $user,
            'course' => $outline['course'],
            'badges' => array_map(static fn ($userBadge) => $userBadge->toArray(), $badges),
        ];
    }

    private function generateVerificationHash(int $userId, int $courseId): string
    {
        do {
            $hash = hash('sha256', $userId . ':' . $courseId . ':' . bin2hex(random_bytes(16)));
            $hash = substr($hash, 0, 32);
        } while ($this->certificateRepo->findByHash($hash) !== null);

        return $hash;
    }
}
