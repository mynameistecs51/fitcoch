<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\ValidationException;

class UserController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserService $userService,
    ) {
    }

    public function me(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $profile = $this->userService->getCurrentUserProfile($userId);

        if ($profile === null) {
            return Response::apiError('UNAUTHORIZED', __('errors.unauthorized'), 401);
        }

        return Response::apiSuccess($profile);
    }

    public function showProfile(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);

        return Response::view('auth/profile', [
            'title' => __('profile.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'success' => $request->query()['success'] ?? null,
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $data = $request->all();

        if (!verify_csrf_token($data['csrf_token'] ?? null)) {
            $roles = $this->authService->getUserRoles($user->id);

            return Response::view('auth/profile', [
                'title' => __('profile.title'),
                'user' => $user,
                'roles' => $roles,
                'isAdmin' => in_array('admin', $roles, true),
                'error' => __('errors.invalid_csrf'),
            ]);
        }

        try {
            $updatedUser = $this->userService->updateProfile($user->id, $data);
        } catch (ValidationException $e) {
            $roles = $this->authService->getUserRoles($user->id);

            return Response::view('auth/profile', [
                'title' => __('profile.title'),
                'user' => $user,
                'roles' => $roles,
                'isAdmin' => in_array('admin', $roles, true),
                'errors' => $e->errors(),
                'form' => $data,
            ]);
        }

        return Response::redirect('/profile?' . http_build_query(['success' => 1]));
    }

    public function instructorPing(Request $request): Response
    {
        return Response::apiSuccess(['message' => __('api.instructor_granted')]);
    }
}
