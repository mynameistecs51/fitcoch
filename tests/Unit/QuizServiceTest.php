<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\ReadinessTicket;
use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\ReadinessTicketRepository;
use App\Services\QuizService;
use App\Services\ValidationException;
use PHPUnit\Framework\TestCase;

class QuizServiceTest extends TestCase
{
    private function createService(
        ?QuizRepository $quizRepo = null,
        ?QuizAttemptRepository $attemptRepo = null,
        ?ReadinessTicketRepository $ticketRepo = null,
        ?ModuleRepository $moduleRepo = null,
        ?CourseRepository $courseRepo = null,
        ?CohortRepository $cohortRepo = null,
    ): QuizService {
        return new QuizService(
            $quizRepo ?? $this->createMock(QuizRepository::class),
            $attemptRepo ?? $this->createMock(QuizAttemptRepository::class),
            $ticketRepo ?? $this->createMock(ReadinessTicketRepository::class),
            $moduleRepo ?? $this->createMock(ModuleRepository::class),
            $courseRepo ?? $this->createMock(CourseRepository::class),
            $cohortRepo ?? $this->createMock(CohortRepository::class),
        );
    }

    public function testSubmitAttemptUnlocksReadinessTicketWhenPassed(): void
    {
        $quiz = new Quiz(1, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');
        $module = new Module(10, 5, 'Unit 1', 1, 'now');
        $course = new Course(5, 'Course', null, 'published', 'now', 'now');
        $cohort = new Cohort(2, 5, 'Cohort A', '2026-01-01', '2026-06-30', 'now', 'now');
        $question = new Question(100, 1, 'Q1', 'single_choice', 10, [
            ['option_number' => 1, 'option_text' => 'A', 'is_correct' => true],
            ['option_number' => 2, 'option_text' => 'B', 'is_correct' => false],
        ]);
        $ticket = new ReadinessTicket(7, 2, 10, 'unlocked', null, null, 'now');

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('findById')->willReturn($quiz);
        $quizRepo->method('listQuestionsWithOptions')->willReturn([$question]);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('findById')->willReturn($module);

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);
        $courseRepo->method('isUserEnrolled')->willReturn(true);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findActiveEnrollmentForUser')->willReturn($cohort);

        $attemptRepo = $this->createMock(QuizAttemptRepository::class);
        $attemptRepo->expects($this->once())->method('create')->willReturn([
            'id' => 501,
            'score_pct' => 100,
            'completed_at' => 'now',
        ]);

        $ticketRepo = $this->createMock(ReadinessTicketRepository::class);
        $ticketRepo->method('find')->willReturn(null);
        $ticketRepo->expects($this->once())->method('unlock')->willReturn($ticket);

        $service = $this->createService($quizRepo, $attemptRepo, $ticketRepo, $moduleRepo, $courseRepo, $cohortRepo);
        $result = $service->submitAttempt(1, 7, [
            'responses' => [
                ['question_id' => 100, 'selected_option_number' => 1],
            ],
        ]);

        $this->assertTrue($result['passed']);
        $this->assertSame(100, $result['score_pct']);
        $this->assertSame('unlocked', $result['readiness_ticket']['status']);
    }

    public function testSubmitAttemptRequiresAllAnswers(): void
    {
        $quiz = new Quiz(1, 10, 'readiness', 'Unit 1 Quiz', 80, 'now');
        $module = new Module(10, 5, 'Unit 1', 1, 'now');
        $course = new Course(5, 'Course', null, 'published', 'now', 'now');
        $cohort = new Cohort(2, 5, 'Cohort A', '2026-01-01', '2026-06-30', 'now', 'now');
        $question = new Question(100, 1, 'Q1', 'single_choice', 10, [
            ['option_number' => 1, 'option_text' => 'A', 'is_correct' => true],
        ]);

        $quizRepo = $this->createMock(QuizRepository::class);
        $quizRepo->method('findById')->willReturn($quiz);
        $quizRepo->method('listQuestionsWithOptions')->willReturn([$question]);

        $moduleRepo = $this->createMock(ModuleRepository::class);
        $moduleRepo->method('findById')->willReturn($module);

        $courseRepo = $this->createMock(CourseRepository::class);
        $courseRepo->method('findById')->willReturn($course);
        $courseRepo->method('isUserEnrolled')->willReturn(true);

        $cohortRepo = $this->createMock(CohortRepository::class);
        $cohortRepo->method('findActiveEnrollmentForUser')->willReturn($cohort);

        $service = $this->createService($quizRepo, null, null, $moduleRepo, $courseRepo, $cohortRepo);

        $this->expectException(ValidationException::class);
        $service->submitAttempt(1, 7, ['responses' => []]);
    }
}
