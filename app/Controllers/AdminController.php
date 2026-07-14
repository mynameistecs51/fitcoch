<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\ValidationException;
use Exception;

class AdminController
{
    public function __construct(
        private readonly AdminService $adminService,
        private readonly AuthService $authService,
    ) {
    }

    /** @return array{user: ?\App\Models\User, isAdmin: bool, roles: array<int, string>} */
    private function layoutContext(): array
    {
        $user = $this->authService->currentUser();
        $roles = $user ? $this->authService->getUserRoles($user->id) : [];

        return [
            'user' => $user,
            'isAdmin' => true,
            'roles' => $roles,
        ];
    }

    public function index(Request $request): Response
    {
        return Response::view('admin/users/index', array_merge($this->layoutContext(), [
            'title' => __('admin.title'),
            'accounts' => $this->adminService->listAccounts(),
            'success' => $request->query()['success'] ?? null,
            'error' => $request->query()['error'] ?? null,
        ]));
    }

    public function edit(Request $request, int $id): Response
    {
        $accounts = $this->adminService->listAccounts();
        $account = null;

        foreach ($accounts as $entry) {
            if ($entry['user']->id === $id) {
                $account = $entry;
                break;
            }
        }

        if ($account === null) {
            return Response::redirect('/admin/users?error=not_found');
        }

        return Response::view('admin/users/edit', array_merge($this->layoutContext(), [
            'title' => __('admin.edit_title'),
            'account' => $account,
            'availableRoles' => $this->adminService->listAvailableRoles(),
            'errors' => [],
            'error' => $request->query()['error'] ?? null,
        ]));
    }

    public function updateRoles(Request $request, int $id): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/admin/users/' . $id . '?error=csrf');
        }

        $actorId = (int) $request->getAttribute('user_id', 0);
        $roles = $request->input('roles', []);

        if (!is_array($roles)) {
            $roles = [];
        }

        try {
            $this->adminService->updateUserRoles($actorId, $id, array_map('strval', $roles));
        } catch (ValidationException $e) {
            return $this->renderEditWithErrors($id, $e->errors(), $request);
        } catch (Exception $e) {
            return Response::redirect('/admin/users/' . $id . '?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/admin/users?success=roles_updated');
    }

    public function updateStatus(Request $request, int $id): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/admin/users/' . $id . '?error=csrf');
        }

        $actorId = (int) $request->getAttribute('user_id', 0);
        $status = (string) $request->input('status', '');

        try {
            $this->adminService->updateUserStatus($actorId, $id, $status);
        } catch (ValidationException $e) {
            return $this->renderEditWithErrors($id, $e->errors(), $request);
        } catch (Exception $e) {
            return Response::redirect('/admin/users/' . $id . '?error=' . urlencode($e->getMessage()));
        }

        return Response::redirect('/admin/users?success=status_updated');
    }

    public function apiListUsers(Request $request): Response
    {
        $accounts = $this->adminService->listAccounts();

        $data = array_map(static function (array $entry): array {
            return [
                'id' => $entry['user']->id,
                'email' => $entry['user']->email,
                'first_name' => $entry['user']->firstName,
                'last_name' => $entry['user']->lastName,
                'status' => $entry['user']->status,
                'roles' => $entry['roles'],
            ];
        }, $accounts);

        return Response::apiSuccess($data);
    }

    /** @param array<string, array<int, string>> $errors */
    private function renderEditWithErrors(int $id, array $errors, Request $request): Response
    {
        $accounts = $this->adminService->listAccounts();
        $account = null;

        foreach ($accounts as $entry) {
            if ($entry['user']->id === $id) {
                $account = $entry;
                break;
            }
        }

        if ($account === null) {
            return Response::redirect('/admin/users?error=not_found');
        }

        $selectedRoles = $request->input('roles', $account['roles']);
        if (!is_array($selectedRoles)) {
            $selectedRoles = $account['roles'];
        }

        $account['roles'] = array_map('strval', $selectedRoles);

        return Response::view('admin/users/edit', array_merge($this->layoutContext(), [
            'title' => __('admin.edit_title'),
            'account' => $account,
            'availableRoles' => $this->adminService->listAvailableRoles(),
            'errors' => $errors,
            'error' => null,
        ]));
    }
}
