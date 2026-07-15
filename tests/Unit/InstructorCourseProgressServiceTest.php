<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\CohortRepository;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Services\CourseService;
use App\Services\InstructorCourseProgressService;
use App\Services\QuizService;
use PHPUnit\Framework\TestCase;

class InstructorCourseProgressServiceTest extends TestCase
{
    public function testBuildCourseReportAggregatesLearnerAndModuleStats(): void
    {
        $course = new Course(1, 'Test Course', 'Desc', 'published', 'now', 'now');
        $module = new Module(10, 1, 'Unit 1', 1, 'now');
        $nugget = new Nugget(100, 10, 'Lesson 1', 'video', 'https://youtu.be/a', null, 180, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseForInstructor')->willReturn([
            'course' => $course,
            'modules' => [$module],
            'nuggetsByModule' => [10 => [$nugget]],
        ]);

        $quizService = $this->createMock(QuizService::class);
        $quizService->method('listQuizzesByModuleIds')->willReturn([10 => $quiz]);

        $cohort = new \App\Models\Cohort(5, 1, 'Cohort A', '2026-01-01', '2026-12-31', 'now', 'now');

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('listByCourseId')->willReturn([$cohort]);
        $cohortRepo->method('listActiveEnrollments')->willReturn([
            [
                'user_id' => 7,
                'first_name' => 'Som',
                'last_name' => 'Chai',
                'email' => 'som@example.com',
                'enrolled_at' => '2026-01-02 10:00:00',
                'status' => 'active',
            ],
            [
                'user_id' => 8,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane@example.com',
                'enrolled_at' => '2026-01-03 10:00:00',
                'status' => 'active',
            ],
        ]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByCohortAndNuggetIds')->willReturn([
            7 => [
                100 => [
                    'user_id' => 7,
                    'nugget_id' => 100,
                    'progress_percentage' => 100,
                    'status' => 'completed',
                    'completed_at' => 'now',
                    'updated_at' => 'now',
                ],
            ],
            8 => [
                100 => [
                    'user_id' => 8,
                    'nugget_id' => 100,
                    'progress_percentage' => 40,
                    'status' => 'in_progress',
                    'completed_at' => null,
                    'updated_at' => 'now',
                ],
            ],
        ]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByCohortAndQuizIds')->willReturn([
            7 => [
                50 => [
                    'id' => 1,
                    'quiz_id' => 50,
                    'score_pct' => 90,
                    'completed_at' => 'now',
                ],
            ],
            8 => [
                50 => [
                    'id' => 2,
                    'quiz_id' => 50,
                    'score_pct' => 50,
                    'completed_at' => 'now',
                ],
            ],
        ]);

        $service = new InstructorCourseProgressService(
            $courseService,
            $quizService,
            $cohortRepo,
            $progressRepo,
            $attemptRepo,
        );

        $report = $service->buildCourseReport(1);

        $this->assertNotNull($report);
        $this->assertSame(2, $report['summary']['total_enrolled']);
        $this->assertSame(70, $report['summary']['avg_progress_pct']);
        $this->assertSame(1, $report['modules'][0]['learners_passed']);
        $this->assertSame(1, $report['modules'][0]['learners_failed']);
        $this->assertSame('passed', $report['learners'][0]['modules'][0]['status']);
        $this->assertSame('failed', $report['learners'][1]['modules'][0]['status']);
    }
}
