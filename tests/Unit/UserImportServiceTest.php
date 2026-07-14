<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\UserImportService;
use PHPUnit\Framework\TestCase;

class UserImportServiceTest extends TestCase
{
    public function testBuildTemplateBinaryCreatesXlsxArchive(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive extension is not available.');
        }

        $service = new UserImportService(
            $this->createMock(UserRepository::class),
            $this->createMock(RoleRepository::class),
        );

        $binary = $service->buildTemplateBinary();
        $tempFile = tempnam(sys_get_temp_dir(), 'fitcoch-import-test-');

        $this->assertNotFalse($tempFile);
        file_put_contents($tempFile, $binary);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempFile) === true);
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
        @unlink($tempFile);
    }

    public function testImportRowsSkipsDuplicateEmail(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $roleRepo = $this->createMock(RoleRepository::class);

        $userRepo->method('emailExists')->willReturn(true);

        $service = new UserImportService($userRepo, $roleRepo);

        $result = $service->importRows([
            ['first_name', 'last_name', 'email', 'password', 'role'],
            ['Somchai', 'Jaidee', 'somchai@example.com', 'ChangeMe123!', 'learner'],
        ]);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertArrayHasKey(2, $result['errors']);
    }
}
