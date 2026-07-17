<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Models\User;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\UserRepository;
use App\Services\CertificateService;
use App\Services\CourseService;
use App\Services\GamificationService;
use App\Services\LearnerDashboardService;
use App\Services\LessonNavigationService;
use App\Services\QuizService;
use App\Services\SpacedRepetitionService;
use PHPUnit\Framework\TestCase;

class LearnerDashboardServiceTest extends TestCase
{
    public function testBuildOverviewIncludesFailedQuizRetakeItem(): void
    {
        $course = new Course(1, 'Test Course', 'Desc', 'published', 'now', 'now');
        $module = new Module(10, 1, 'Unit 1', 1, 'now');
        $nugget = new Nugget(100, 10, 'Lesson 1', 'video', 'https://youtu.be/a', null, 180, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('listEnrolledCourses')->willReturn([$course]);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$module],
            'nuggetsByModule' => [10 => [$nugget]],
        ]);

        $quizService = $this->createMock(QuizService::class);
        $quizService->method('listQuizzesByModuleIds')->willReturn([10 => $quiz]);

        $lessonNavigation = $this->createMock(LessonNavigationService::class);
        $lessonNavigation->method('findResumeNuggetId')->willReturn(100);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([
            100 => [
                'user_id' => 7,
                'nugget_id' => 100,
                'progress_percentage' => 100,
                'status' => 'completed',
                'completed_at' => 'now',
                'updated_at' => 'now',
            ],
        ]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByUserAndQuizIds')->willReturn([
            50 => [
                'id' => 1,
                'quiz_id' => 50,
                'score_pct' => 40,
                'completed_at' => 'now',
            ],
        ]);

        $reviewService = $this->createMock(SpacedRepetitionService::class);
        $reviewService->method('countDueToday')->willReturn(3);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findById')->willReturn(new User(
            7,
            '6501234567',
            'นาย',
            'learner@test.com',
            'hash',
            'Test',
            'User',
            'active',
            'Asia/Bangkok',
            'now',
            'now',
        ));

        $gamificationService = $this->createMock(GamificationService::class);
        $gamificationService->method('getSummary')->willReturn([
            'current_streak' => 0,
            'longest_streak' => 0,
            'total_xp' => 0,
            'badges' => [],
        ]);

        $certificateService = $this->createMock(CertificateService::class);
        $certificateService->method('findLearnerCertificate')->willReturn(null);
        $certificateService->method('isEligible')->willReturn(false);

        $service = new LearnerDashboardService(
            $courseService,
            $quizService,
            $lessonNavigation,
            $reviewService,
            $progressRepo,
            $attemptRepo,
            $userRepo,
            $gamificationService,
            $certificateService,
        );

        $overview = $service->buildOverview(7);

        $this->assertSame(1, $overview['summary']['enrolled_courses']);
        $this->assertSame(1, $overview['summary']['lessons_completed']);
        $this->assertSame(0, $overview['summary']['quizzes_passed']);
        $this->assertSame(3, $overview['summary']['reviews_due']);
        $this->assertCount(1, $overview['retake_items']);
        $this->assertSame('failed', $overview['courses'][0]['modules'][0]['status']);
    }
}
