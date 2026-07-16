<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\CertificateRepository;
use App\Repositories\GamificationRepository;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\UserRepository;
use App\Services\CertificateService;
use App\Services\CourseService;
use App\Services\QuizService;
use PHPUnit\Framework\TestCase;

class CertificateServiceTest extends TestCase
{
    public function testIsEligibleReturnsFalseWhenLessonsIncomplete(): void
    {
        $course = new Course(1, 'Course', null, 'published', 'now', 'now');
        $module = new Module(10, 1, 'Unit 1', 1, 'now');
        $nugget = new Nugget(100, 10, 'Lesson', 'video', 'url', null, 180, 1, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$module],
            'nuggetsByModule' => [10 => [$nugget]],
        ]);

        $quizService = $this->createMock(QuizService::class);
        $quizService->method('listQuizzesByModuleIds')->willReturn([]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([
            100 => [
                'status' => 'in_progress',
                'progress_percentage' => 50,
            ],
        ]);

        $service = new CertificateService(
            $this->createMock(CertificateRepository::class),
            $courseService,
            $quizService,
            $progressRepo,
            $this->createMock(QuizAttemptRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(GamificationRepository::class),
        );

        $this->assertFalse($service->isEligible(7, 1));
    }

    public function testIsEligibleReturnsTrueWhenLessonsAndQuizzesComplete(): void
    {
        $course = new Course(1, 'Course', null, 'published', 'now', 'now');
        $module = new Module(10, 1, 'Unit 1', 1, 'now');
        $nugget = new Nugget(100, 10, 'Lesson', 'video', 'url', null, 180, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$module],
            'nuggetsByModule' => [10 => [$nugget]],
        ]);

        $quizService = $this->createMock(QuizService::class);
        $quizService->method('listQuizzesByModuleIds')->willReturn([10 => $quiz]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([
            100 => ['status' => 'completed', 'progress_percentage' => 100],
        ]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByUserAndQuizIds')->willReturn([
            50 => ['score_pct' => 90],
        ]);

        $service = new CertificateService(
            $this->createMock(CertificateRepository::class),
            $courseService,
            $quizService,
            $progressRepo,
            $attemptRepo,
            $this->createMock(UserRepository::class),
            $this->createMock(GamificationRepository::class),
        );

        $this->assertTrue($service->isEligible(7, 1));
    }
}
