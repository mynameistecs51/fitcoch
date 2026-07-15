<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\QuizImportService;
use App\Services\QuizService;
use PHPUnit\Framework\TestCase;

class QuizImportServiceTest extends TestCase
{
    public function testBuildTemplateBinaryReturnsNonEmptyXlsx(): void
    {
        $service = new QuizImportService($this->createMock(QuizService::class));
        $binary = $service->buildTemplateBinary();

        $this->assertNotSame('', $binary);
        $this->assertStringStartsWith('PK', $binary);
    }

    public function testImportRowsReturnsErrorsForInvalidRow(): void
    {
        $quizService = $this->createMock(QuizService::class);
        $quizService->expects($this->never())->method('saveQuestions');

        $service = new QuizImportService($quizService);
        $result = $service->importRows(1, 1, 1, [
            ['question_text', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_option', 'points'],
            ['', 'A', 'B', 'C', 'D', '1', '10'],
        ]);

        $this->assertSame(0, $result['created']);
        $this->assertArrayHasKey(2, $result['errors']);
    }
}
