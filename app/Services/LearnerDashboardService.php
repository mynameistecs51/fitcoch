<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\UserRepository;

class LearnerDashboardService
{
    public function __construct(
        private readonly CourseService $courseService,
        private readonly QuizService $quizService,
        private readonly LessonNavigationService $lessonNavigationService,
        private readonly SpacedRepetitionService $reviewService,
        private readonly NuggetProgressRepository $progressRepo,
        private readonly QuizAttemptRepository $attemptRepo,
        private readonly UserRepository $userRepo,
        private readonly GamificationService $gamificationService,
        private readonly CertificateService $certificateService,
    ) {
    }

    /**
     * @return array{
     *     summary: array{
     *         enrolled_courses: int,
     *         overall_progress: int,
     *         lessons_completed: int,
     *         lessons_total: int,
     *         quizzes_passed: int,
     *         quizzes_total: int,
     *         average_quiz_score: ?int,
     *         reviews_due: int
     *     },
     *     courses: array<int, array{
     *         course: Course,
     *         progress_pct: int,
     *         lessons_completed: int,
     *         lessons_total: int,
     *         resume_url: ?string,
     *         modules: array<int, array{
     *             module: Module,
     *             video_nugget: ?Nugget,
     *             quiz: ?Quiz,
     *             latest_score: ?int,
     *             passing_score: ?int,
     *             passed: ?bool,
     *             lesson_url: ?string,
     *             quiz_url: ?string,
     *             status: string
     *         }>
     *     }>,
     *     retake_items: array<int, array{
     *         course_title: string,
     *         module_title: string,
     *         quiz_title: string,
     *         score_pct: int,
     *         passing_score: int,
     *         lesson_url: ?string,
     *         quiz_url: string
     *     }>
     * }
     */
    public function buildOverview(int $userId): array
    {
        $user = $this->userRepo->findById($userId);
        $reviewsDue = $user !== null ? $this->reviewService->countDueToday($user) : 0;

        $enrolledCourses = $this->courseService->listEnrolledCourses($userId);
        $courses = [];
        $retakeItems = [];
        $lessonsCompleted = 0;
        $lessonsTotal = 0;
        $quizzesPassed = 0;
        $quizzesTotal = 0;
        $quizScoreTotal = 0;
        $quizScoreCount = 0;
        $progressTotal = 0;

        foreach ($enrolledCourses as $course) {
            $outline = $this->courseService->getCourseOutline($course->id, $userId);

            if ($outline === null) {
                continue;
            }

            $moduleIds = array_map(static fn (Module $module): int => $module->id, $outline['modules']);
            $quizzesByModule = $this->quizService->listQuizzesByModuleIds($moduleIds);
            $quizIds = array_values(array_map(static fn (Quiz $quiz): int => $quiz->id, array_filter($quizzesByModule)));
            $latestAttempts = $this->attemptRepo->findLatestByUserAndQuizIds($userId, $quizIds);

            $videoNuggets = [];

            foreach ($outline['modules'] as $module) {
                foreach ($outline['nuggetsByModule'][$module->id] ?? [] as $nugget) {
                    if ($nugget->nuggetType === 'video') {
                        $videoNuggets[] = $nugget;
                    }
                }
            }

            $nuggetIds = array_map(static fn (Nugget $nugget): int => $nugget->id, $videoNuggets);
            $progressByNugget = $this->progressRepo->listByUserAndNuggetIds($userId, $nuggetIds);

            $courseLessonsCompleted = 0;
            $courseProgressTotal = 0;
            $moduleRows = [];

            foreach ($outline['modules'] as $module) {
                $moduleNuggets = $outline['nuggetsByModule'][$module->id] ?? [];
                $videoNugget = null;

                foreach ($moduleNuggets as $nugget) {
                    if ($nugget->nuggetType === 'video') {
                        $videoNugget = $nugget;
                        break;
                    }
                }

                $quiz = $quizzesByModule[$module->id] ?? null;
                $latestAttempt = $quiz !== null ? ($latestAttempts[$quiz->id] ?? null) : null;
                $passed = null;
                $status = 'not_started';
                $lessonUrl = $videoNugget !== null ? url('/nuggets/' . $videoNugget->id) : null;
                $quizUrl = $quiz !== null ? url('/quizzes/' . $quiz->id) : null;

                if ($videoNugget !== null) {
                    $lessonsTotal++;
                    $progress = $progressByNugget[$videoNugget->id] ?? null;
                    $progressPct = (int) ($progress['progress_percentage'] ?? 0);
                    $isCompleted = ($progress['status'] ?? '') === 'completed';

                    if ($isCompleted) {
                        $courseLessonsCompleted++;
                        $lessonsCompleted++;
                        $courseProgressTotal += 100;
                        $progressTotal += 100;
                    } else {
                        $courseProgressTotal += $progressPct;
                        $progressTotal += $progressPct;

                        if ($progressPct > 0) {
                            $status = 'in_progress';
                        }
                    }
                }

                if ($quiz !== null) {
                    $quizzesTotal++;

                    if ($latestAttempt !== null) {
                        $quizScoreTotal += $latestAttempt['score_pct'];
                        $quizScoreCount++;
                        $passed = $latestAttempt['score_pct'] >= $quiz->passingScorePct;

                        if ($passed) {
                            $quizzesPassed++;
                            $status = 'passed';
                        } else {
                            $status = 'failed';
                            $retakeItems[] = [
                                'course_title' => $course->title,
                                'module_title' => $module->title,
                                'quiz_title' => $quiz->title,
                                'score_pct' => $latestAttempt['score_pct'],
                                'passing_score' => $quiz->passingScorePct,
                                'lesson_url' => $lessonUrl,
                                'quiz_url' => $quizUrl,
                            ];
                        }
                    } elseif ($status === 'in_progress') {
                        $status = 'in_progress';
                    }
                } elseif ($videoNugget !== null && ($progressByNugget[$videoNugget->id]['status'] ?? '') === 'completed') {
                    $status = 'passed';
                } elseif ($videoNugget !== null && ($progressByNugget[$videoNugget->id]['progress_percentage'] ?? 0) > 0) {
                    $status = 'in_progress';
                }

                $moduleRows[] = [
                    'module' => $module,
                    'video_nugget' => $videoNugget,
                    'quiz' => $quiz,
                    'latest_score' => $latestAttempt['score_pct'] ?? null,
                    'passing_score' => $quiz?->passingScorePct,
                    'passed' => $passed,
                    'lesson_url' => $lessonUrl,
                    'quiz_url' => $quizUrl,
                    'status' => $status,
                ];
            }

            $courseLessonCount = count($videoNuggets);
            $courseProgressPct = $courseLessonCount > 0
                ? (int) round($courseProgressTotal / $courseLessonCount)
                : 0;
            $resumeNuggetId = $this->lessonNavigationService->findResumeNuggetId($course->id, $userId);
            $existingCertificate = $this->certificateService->findLearnerCertificate($userId, $course->id);
            $certificateUrl = null;

            if ($existingCertificate !== null) {
                $certificateUrl = url('/certificate/' . $existingCertificate->verificationHash);
            } elseif ($this->certificateService->isEligible($userId, $course->id)) {
                $certificateUrl = url('/courses/' . $course->id . '/certificate');
            }

            $courses[] = [
                'course' => $course,
                'progress_pct' => $courseProgressPct,
                'lessons_completed' => $courseLessonsCompleted,
                'lessons_total' => $courseLessonCount,
                'resume_url' => $resumeNuggetId !== null ? url('/nuggets/' . $resumeNuggetId) : null,
                'certificate_url' => $certificateUrl,
                'modules' => $moduleRows,
            ];
        }

        $enrolledCount = count($courses);
        $overallProgress = $lessonsTotal > 0 ? (int) round($progressTotal / $lessonsTotal) : 0;
        $gamification = $this->gamificationService->getSummary($userId);

        return [
            'summary' => [
                'enrolled_courses' => $enrolledCount,
                'overall_progress' => $overallProgress,
                'lessons_completed' => $lessonsCompleted,
                'lessons_total' => $lessonsTotal,
                'quizzes_passed' => $quizzesPassed,
                'quizzes_total' => $quizzesTotal,
                'average_quiz_score' => $quizScoreCount > 0 ? (int) round($quizScoreTotal / $quizScoreCount) : null,
                'reviews_due' => $reviewsDue,
                'current_streak' => $gamification['current_streak'],
                'longest_streak' => $gamification['longest_streak'],
                'total_xp' => $gamification['total_xp'],
            ],
            'courses' => $courses,
            'retake_items' => $retakeItems,
            'gamification' => $gamification,
        ];
    }
}
