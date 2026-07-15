<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\QuizImportService;
use App\Services\QuizService;
use App\Services\ValidationException;
use Exception;

class InstructorQuizController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly QuizService $quizService,
        private readonly QuizImportService $quizImportService,
    ) {
    }

    public function edit(Request $request, int $courseId, int $moduleId): Response
    {
        $editor = $this->quizService->getQuizEditor($courseId, $moduleId);

        if ($editor === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);
        $importResult = $_SESSION['quiz_import_result'] ?? null;
        $importError = $_SESSION['quiz_import_error'] ?? null;
        unset($_SESSION['quiz_import_result'], $_SESSION['quiz_import_error']);

        return Response::view('instructor/quiz/form', [
            'title' => __('quizzes.instructor.editor_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $editor['course'],
            'module' => $editor['module'],
            'quiz' => $editor['quiz'],
            'questions' => $editor['questions'],
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
            'errors' => [],
            'form' => [],
            'questionForm' => [],
            'questionsForm' => [],
            'questionErrors' => [],
            'importResult' => is_array($importResult) ? $importResult : null,
            'importError' => is_string($importError) ? $importError : null,
        ]);
    }

    public function downloadImportTemplate(Request $request, int $courseId, int $moduleId): Response
    {
        $editor = $this->quizService->getQuizEditor($courseId, $moduleId);

        if ($editor === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        unset($_SESSION['quiz_import_error']);

        return Response::download(
            $this->quizImportService->buildTemplateBinary(),
            $this->quizImportService->templateFilename(),
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    public function importQuestions(Request $request, int $courseId, int $moduleId, int $quizId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            $_SESSION['quiz_import_error'] = __('errors.invalid_csrf');
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz');
        }

        try {
            $file = $request->file('quiz_file');

            if ($file === null) {
                throw new Exception(__('quizzes.import.validation.file_required'));
            }

            $result = $this->quizImportService->importUploadedFile($courseId, $moduleId, $quizId, $file);
            $_SESSION['quiz_import_result'] = $result;
            unset($_SESSION['quiz_import_error']);
        } catch (Exception $e) {
            $_SESSION['quiz_import_error'] = $e->getMessage();
            unset($_SESSION['quiz_import_result']);
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz');
    }

    public function saveQuiz(Request $request, int $courseId, int $moduleId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=csrf');
        }

        try {
            $this->quizService->saveQuiz($courseId, $moduleId, $request->all());
        } catch (ValidationException $e) {
            return $this->renderWithErrors($courseId, $moduleId, $e->errors(), $request->all(), [], [], []);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?success=quiz_saved');
    }

    public function deleteQuiz(Request $request, int $courseId, int $moduleId, int $quizId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=csrf');
        }

        try {
            $this->quizService->deleteQuiz($courseId, $moduleId, $quizId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?success=quiz_deleted');
    }

    public function saveQuestion(Request $request, int $courseId, int $moduleId, int $quizId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=csrf');
        }

        try {
            $created = $this->quizService->saveQuestions($courseId, $moduleId, $quizId, $request->all());
        } catch (ValidationException $e) {
            return $this->renderWithErrors($courseId, $moduleId, [], [], [], $request->all()['questions'] ?? [], $e->errors());
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=' . urlencode($e->getMessage()));
        }

        $count = count($created);

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?success=questions_saved&count=' . $count);
    }

    public function deleteQuestion(Request $request, int $courseId, int $moduleId, int $quizId, int $questionId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=csrf');
        }

        try {
            $this->quizService->deleteQuestion($courseId, $moduleId, $quizId, $questionId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/modules/' . $moduleId . '/quiz?success=question_deleted');
    }

    /** @param array<string, array<int, string>> $errors */
    private function renderWithErrors(
        int $courseId,
        int $moduleId,
        array $errors,
        array $form,
        array $questionForm,
        array $questionsForm,
        array $questionErrors,
    ): Response {
        $editor = $this->quizService->getQuizEditor($courseId, $moduleId);

        if ($editor === null) {
            return Response::redirect('/instructor/courses/' . $courseId . '/edit?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/quiz/form', [
            'title' => __('quizzes.instructor.editor_title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $editor['course'],
            'module' => $editor['module'],
            'quiz' => $editor['quiz'],
            'questions' => $editor['questions'],
            'success' => null,
            'error' => null,
            'errors' => $errors,
            'form' => $form,
            'questionForm' => $questionForm,
            'questionsForm' => $questionsForm,
            'questionErrors' => $questionErrors,
            'importResult' => null,
            'importError' => null,
        ]);
    }
}
