<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use App\Models\Nugget;
use App\Models\Quiz;
use App\Repositories\NuggetProgressRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Services\CourseService;
use App\Services\LessonNavigationService;
use App\Services\LessonUnlockService;
use PHPUnit\Framework\TestCase;

class LessonNavigationServiceTest extends TestCase
{
    private function createService(
        CourseService $courseService,
        NuggetProgressRepository $progressRepo,
        QuizRepository $quizRepo,
        QuizAttemptRepository $attemptRepo,
    ): LessonNavigationService {
        $unlockService = new LessonUnlockService(
            $courseService,
            $progressRepo,
            $quizRepo,
            $attemptRepo,
        );

        return new LessonNavigationService($courseService, $unlockService);
    }

    public function testBuildForLearnerUnlocksNextLessonWhenPreviousQuizPassed(): void
    {
        $course = new Course(1, 'Test Course', 'Desc', 'published', 'now', 'now');
        $moduleOne = new Module(10, 1, 'Unit 1', 1, 'now');
        $moduleTwo = new Module(11, 1, 'Unit 2', 2, 'now');
        $nuggetOne = new Nugget(100, 10, 'Lesson 1', 'video', 'https://youtu.be/a', null, 180, 1, 'now');
        $nuggetTwo = new Nugget(101, 11, 'Lesson 2', 'video', 'https://youtu.be/b', null, 240, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$moduleOne, $moduleTwo],
            'nuggetsByModule' => [
                10 => [$nuggetOne],
                11 => [$nuggetTwo],
            ],
        ]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([]);

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('listByModuleIds')->willReturn([10 => $quiz]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByUserAndQuizIds')->willReturn([
            50 => [
                'id' => 1,
                'quiz_id' => 50,
                'score_pct' => 100,
                'completed_at' => 'now',
            ],
        ]);

        $service = $this->createService($courseService, $progressRepo, $quizRepo, $attemptRepo);
        $navigation = $service->buildForLearner(1, 7, 10, null, 50);

        $this->assertNotNull($navigation);
        $this->assertSame('available', $navigation['lessons'][1]['state']);
        $this->assertSame('passed', $navigation['lessons'][0]['quiz_state']);
    }

    public function testBuildForLearnerKeepsNextLessonLockedWhenQuizNotPassed(): void
    {
        $course = new Course(1, 'Test Course', 'Desc', 'published', 'now', 'now');
        $moduleOne = new Module(10, 1, 'Unit 1', 1, 'now');
        $moduleTwo = new Module(11, 1, 'Unit 2', 2, 'now');
        $nuggetOne = new Nugget(100, 10, 'Lesson 1', 'video', 'https://youtu.be/a', null, 180, 1, 'now');
        $nuggetTwo = new Nugget(101, 11, 'Lesson 2', 'video', 'https://youtu.be/b', null, 240, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$moduleOne, $moduleTwo],
            'nuggetsByModule' => [
                10 => [$nuggetOne],
                11 => [$nuggetTwo],
            ],
        ]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([]);

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('listByModuleIds')->willReturn([10 => $quiz]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByUserAndQuizIds')->willReturn([
            50 => [
                'id' => 1,
                'quiz_id' => 50,
                'score_pct' => 40,
                'completed_at' => 'now',
            ],
        ]);

        $service = $this->createService($courseService, $progressRepo, $quizRepo, $attemptRepo);
        $navigation = $service->buildForLearner(1, 7, 10);

        $this->assertNotNull($navigation);
        $this->assertSame('locked', $navigation['lessons'][1]['state']);
    }

    public function testFindResumeNuggetIdReturnsNextAvailableLessonAfterQuizPass(): void
    {
        $course = new Course(1, 'Test Course', 'Desc', 'published', 'now', 'now');
        $moduleOne = new Module(10, 1, 'Unit 1', 1, 'now');
        $moduleTwo = new Module(11, 1, 'Unit 2', 2, 'now');
        $nuggetOne = new Nugget(100, 10, 'Lesson 1', 'video', 'https://youtu.be/a', null, 180, 1, 'now');
        $nuggetTwo = new Nugget(101, 11, 'Lesson 2', 'video', 'https://youtu.be/b', null, 240, 1, 'now');
        $quiz = new Quiz(50, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');

        $courseService = $this->createMock(CourseService::class);
        $courseService->method('getCourseOutline')->willReturn([
            'course' => $course,
            'modules' => [$moduleOne, $moduleTwo],
            'nuggetsByModule' => [
                10 => [$nuggetOne],
                11 => [$nuggetTwo],
            ],
        ]);

        $progressRepo = $this->createMock(NuggetProgressRepository::class);
        $progressRepo->method('listByUserAndNuggetIds')->willReturn([]);

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('listByModuleIds')->willReturn([10 => $quiz]);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->method('findLatestByUserAndQuizIds')->willReturn([
            50 => [
                'id' => 1,
                'quiz_id' => 50,
                'score_pct' => 100,
                'completed_at' => 'now',
            ],
        ]);

        $service = $this->createService($courseService, $progressRepo, $quizRepo, $attemptRepo);

        $this->assertSame(101, $service->findResumeNuggetId(1, 7));
    }
}
