<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\KnowledgeItemService;
use App\Services\ValidationException;
use Exception;

class InstructorKnowledgeItemController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly KnowledgeItemService $knowledgeItemService,
    ) {
    }

    public function index(Request $request, int $courseId): Response
    {
        $panel = $this->knowledgeItemService->getCoursePanel($courseId);

        if ($panel === null) {
            return Response::redirect('/instructor/courses?error=not_found');
        }

        $user = $this->authService->currentUser();
        $roles = $this->authService->getUserRoles($user?->id ?? 0);

        return Response::view('instructor/knowledge-items/index', [
            'title' => __('knowledge_items.instructor.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'course' => $panel['course'],
            'items' => $panel['items'],
            'success' => $request->query()['success'] ?? null,
            'syncCount' => (int) ($request->query()['count'] ?? 0),
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function store(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=csrf');
        }

        try {
            $this->knowledgeItemService->createItem($courseId, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=validation');
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?success=created');
    }

    public function update(Request $request, int $courseId, int $itemId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=csrf');
        }

        try {
            $this->knowledgeItemService->updateItem($courseId, $itemId, $request->all());
        } catch (ValidationException $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=validation');
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?success=updated');
    }

    public function delete(Request $request, int $courseId, int $itemId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=csrf');
        }

        try {
            $this->knowledgeItemService->deleteItem($courseId, $itemId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?success=deleted');
    }

    public function sync(Request $request, int $courseId): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=csrf');
        }

        try {
            $created = $this->knowledgeItemService->syncFromModules($courseId);
        } catch (Exception $e) {
            return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/instructor/courses/' . $courseId . '/knowledge-items?success=synced&count=' . $created);
    }
}
